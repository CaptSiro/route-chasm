<?php

namespace core\database;

use core\App;
use core\collection\Dictionary;
use core\database\buffer\StaticBuffer;
use core\database\pdo\column\Column;
use core\database\pdo\column\ForeignKey;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\parameter\PdoPrimitiveParam;
use core\database\query\Query;
use core\Init;
use JsonSerializable;

abstract class Table extends Init implements JsonSerializable {
    public const ORIGIN_CODE = 'code';
    public const ORIGIN_DATABASE = 'database';



    abstract public static function getTable(): string;

    /**
     * @return array<Column>
     */
    abstract public static function getColumns(): array;



    protected static Database $database;
    protected static ?string $idColumn = null;

    /**
     * Function that is called when class is loaded. Initialize table name, table columns, and database connection
     *
     * <code>Table::init()</code> function gets default database connection from <code>App</code>
     *
     * @return void
     */
    public static function init(): void {
        self::$database = App::getInstance()
            ->getDefaultDatabase();
    }

    public static function getIdColumn(): string {
        if (is_null(static::$idColumn)) {
            foreach (static::getColumns() as $name => $column) {
                if ($column instanceof PrimaryKey) {
                    static::$idColumn = $name;
                    break;
                }
            }
        }

        return static::$idColumn;
    }

    public static function getColumnEnumString(bool $includeIdColumn = true): string {
        $table = '`'. static::getTable() .'`';
        $string = $includeIdColumn
            ? "$table.`". static::getIdColumn() .'`'
            : "";

        $first = !$includeIdColumn;
        foreach (static::getColumns() as $name => $definition) {
            if ($definition->isVirtual()) {
                continue;
            }

            if (!$first) {
                $string .= ', ';
            }

            $string .= "$table.`$name`";

            $first = false;
        }

        return $string;
    }

    public static function isColumnValid(string $column): bool {
        $columns = static::getColumns();
        return isset($columns[$column]) && !$columns[$column]->isVirtual();
    }



    public static function foreignKey(string $alias): ForeignKey {
        return new ForeignKey(static::class, $alias);
    }

    public static function fetch(string|Query|null $additional = null): ?static {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable(). "`";
        return self::$database
            ->fetch(Query::from($sql, $additional), static::class);
    }

    /**
     * @param string|Query|null $additional
     * @return array<static>|null
     */
    public static function fetchAll(string|Query|null $additional = null): ?array {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable(). "`";
        return self::$database
            ->fetchAll(Query::from($sql, $additional), static::class);
    }



    protected static function createConditionally(?self $instance, bool $create = false): ?static {
        if (!is_null($instance)) {
            return $instance;
        }

        if ($create) {
            return new static();
        }

        return null;
    }

    public static function fromId(int $id): ?self {
        if ($id === 0) {
            return new static();
        }

        $_id = new PdoPrimitiveParam($id);
        return self::$database
            ->fetch(
                "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable()
                ."` WHERE `". static::getIdColumn() ."` = $_id",
                static::class
            );
    }

    public static function fromUnique(string $unique): ?self {
        return static::fromId((int) $unique);
    }

    public static function fromRow(array|false $row): ?self {
        if ($row === false) {
            return null;
        }

        $self = new static();
        $self->data = $row;
        $self->setOrigin(self::ORIGIN_DATABASE);
        return $self;
    }




    /**
     * @var array<string> $updated
     */
    private array $updated = [];
    private string $origin = self::ORIGIN_CODE;
    private mixed $id = null;



    public function __construct(
        private array $data = []
    ) {}



    public function __get(string $name): mixed {
        if (!isset(static::getColumns()[$name])) {
            return null;
        }

        $column = static::getColumns()[$name];
        if ($column instanceof ForeignKey) {
            $name = $column->getAlias();
        }

        return $column->transform($this->data[$name] ?? null);
    }

    public function __set(string $column, mixed $value): void {
        if (!static::isColumnValid($column)) {
            return;
        }

        $this->data[$column] = $value;
        $this->updated[] = $column;
    }

    public function set(array $data): static {
        $columns = static::getColumns();

        foreach ($data as $column => $value) {
            if (!static::isColumnValid($column)) {
                continue;
            }

            $this->data[$column] = $value;
            $this->updated[] = $column;
        }

        return $this;
    }

    public function setOrigin(string $origin): static {
        $this->origin = $origin;
        return $this;
    }

    public function isFromDatabase(): bool {
        return $this->origin === self::ORIGIN_DATABASE;
    }

    public function setDictionary(Dictionary $dictionary): static {
        $columns = static::getColumns();

        foreach ($columns as $column => $definition) {
            if (!static::isColumnValid($column) || !$dictionary->exists($column)) {
                continue;
            }

            $this->data[$column] = $dictionary->get($column);
            $this->updated[] = $column;
        }

        return $this;
    }

    public function save(): void {
        if (empty($this->updated)) {
            return;
        }

        if (!$this->isFromDatabase()) {
            $this->insert();
            return;
        }

        $sql = "UPDATE `". static::getTable() ."` SET ";
        $params = [];
        $first = true;

        foreach (array_unique($this->updated) as $column) {
            if ($column === self::$idColumn) {
                continue;
            }

            if (!$first) {
                $sql .= ', ';
            }

            $sql .= "`$column` = ". StaticBuffer::PARAM_IDENT;
            $params[] = $this->data[$column];

            $first = false;
        }

        $sql .= ' WHERE `'. static::getIdColumn() .'` = '. $this->getId();

        self::$database
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    protected function insert(): void {
        $columns = [];
        $params = [];
        $values = "";
        $first = true;

        foreach (static::getColumns() as $name => $definition) {
            if ($definition->isVirtual()) {
                continue;
            }

            if (!$first) {
                $values .= ', ';
            }

            $columns[] = "`$name`";
            $params[] = $this->data[$name] ?? null;
            $values .= StaticBuffer::PARAM_IDENT;

            $first = false;
        }

        $sql = 'INSERT INTO `'. static::getTable() ."` (". implode(', ', $columns) .") VALUES (". $values .")";
        self::$database
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    public function delete(): void {
        $_id = new PdoPrimitiveParam($this->getId());
        self::$database
            ->run("DELETE FROM `". static::getTable() ."` WHERE `". static::getIdColumn() ."` = $_id");
    }

    public function getId(): int {
        if (isset($this->id)) {
            return $this->id;
        }

        return $this->id = $this->data[static::getIdColumn()] ?? null;
    }

    public function jsonSerialize(): array {
        return $this->data;
    }
}
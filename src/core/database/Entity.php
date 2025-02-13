<?php
/** @noinspection ALL */

namespace core\database;

use core\collection\Dictionary;
use core\database\buffer\StaticBuffer;
use core\database\column\ForeignKey;
use core\database\pdo\parameter\PdoPrimitiveParam;
use core\database\pdo\PdoTable;
use core\database\query\Query;
use core\Init;
use JsonSerializable;

abstract class Entity extends Init implements JsonSerializable {
    public const ORIGIN_CODE = 'code';
    public const ORIGIN_DATABASE = 'database';
    public const VIRTUALITY_CHECK = 'defined-value'; // code smell



    public static function getTableDefinition(): TableDefinition {
        throw new \Exception(__CLASS__ .'::'. __FUNCTION__ . ' is not implemented.');
    }



    /**
     * Function that is called when class is loaded. Initialize table definition
     *
     * @return void
     */
    public static function init(): void {}

    public static function getIdColumn(): string {
        return static::getTableDefinition()->getIdColumn();
    }

    public static function getColumnEnumString(bool $includeIdColumn = true): string {
        $table = '`'. static::getTableDefinition()->getTable() .'`';
        $string = $includeIdColumn
            ? "$table.`". static::getTableDefinition()->getIdColumn() .'`'
            : "";

        $first = !$includeIdColumn;
        foreach (static::getTableDefinition()->getColumns() as $name => $definition) {
            if ($definition->isVirtual(self::VIRTUALITY_CHECK)) {
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



    public static function foreignKey(string $alias): ForeignKey {
        return new ForeignKey(static::class, $alias);
    }

    public static function fetch(string|Query|null $additional = null): ?static {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTableDefinition()->getTable(). "`";
        return static::getTableDefinition()->getDatabase()
            ->fetch(Query::from($sql, $additional), static::class);
    }

    /**
     * @param string|Query|null $additional
     * @return array<static>|null
     */
    public static function fetchAll(string|Query|null $additional = null): ?array {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTableDefinition()->getTable(). "`";
        return static::getTableDefinition()->getDatabase()
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
        return static::getTableDefinition()->getDatabase()
            ->fetch(
                "SELECT ". static::getColumnEnumString() ." FROM `". static::getTableDefinition()->getTable()
                ."` WHERE `". static::getTableDefinition()->getIdColumn() ."` = $_id",
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
        if (!isset(static::getTableDefinition()->getColumns()[$name])) {
            return null;
        }

        $column = static::getTableDefinition()->getColumns()[$name];
        if ($column instanceof ForeignKey) {
            $name = $column->getAlias();
        }

        return $column->transform($this->data[$name] ?? null);
    }

    public function __set(string $column, mixed $value): void {
        if (!$this->isColumnValid($column, $value)) {
            return;
        }

        $this->data[$column] = $value;
        $this->updated[] = $column;
    }

    public function set(array $data): static {
        $columns = static::getTableDefinition()->getColumns();

        foreach ($data as $column => $value) {
            if (!$this->isColumnValid($column, $value)) {
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
        $columns = static::getTableDefinition()->getColumns();

        foreach ($columns as $column => $definition) {
            if (!$dictionary->exists($column)) {
                continue;
            }

            $value = $dictionary->get($column);

            if (!$this->isColumnValid($column, $value)) {
                continue;
            }

            $this->data[$column] = $value;
            $this->updated[] = $column;
        }

        return $this;
    }

    public function isColumnValid(string $column, mixed $value = null): bool {
        $columns = static::getTableDefinition()->getColumns();
        return isset($columns[$column]) && !$columns[$column]->isVirtual($value ?? $this->data[$column] ?? null);
    }

    public function save(): void {
        if (empty($this->updated)) {
            return;
        }

        if (!$this->isFromDatabase()) {
            $this->insert();
            return;
        }

        $sql = "UPDATE `". static::getTableDefinition()->getTable() ."` SET ";
        $params = [];
        $first = true;

        foreach (array_unique($this->updated) as $column) {
            if ($column === static::getTableDefinition()->getIdColumn()) {
                continue;
            }

            if (!$first) {
                $sql .= ', ';
            }

            $sql .= "`$column` = ". StaticBuffer::PARAM_IDENT;
            $params[] = $this->data[$column];

            $first = false;
        }

        $sql .= ' WHERE `'. static::getTableDefinition()->getIdColumn() .'` = '. $this->getId();

        static::getTableDefinition()->getDatabase()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    protected function insert(): void {
        $columns = [];
        $params = [];
        $values = "";
        $first = true;

        foreach (static::getTableDefinition()->getColumns() as $name => $definition) {
            if ($definition->isVirtual($this->data[$name] ?? null)) {
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

        $sql = 'INSERT INTO `'. static::getTableDefinition()->getTable() ."` (". implode(', ', $columns) .") VALUES (". $values .")";
        static::getTableDefinition()->getDatabase()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    public function delete(): void {
        $_id = new PdoPrimitiveParam($this->getId());
        static::getTableDefinition()->getDatabase()
            ->run("DELETE FROM `". static::getTableDefinition()->getTable() ."` WHERE `". static::getTableDefinition()->getIdColumn() ."` = $_id");
    }

    public function getId(): int {
        if (isset($this->id)) {
            return $this->id;
        }

        return $this->id = $this->data[static::getTableDefinition()->getIdColumn()] ?? null;
    }

    public function jsonSerialize(): array {
        return $this->data;
    }

    public function getData(): array {
        return $this->data;
    }
}
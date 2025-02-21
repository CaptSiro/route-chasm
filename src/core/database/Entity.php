<?php
/** @noinspection ALL */

namespace core\database;

use core\collection\Dictionary;
use core\database\buffer\StaticBuffer;
use core\database\column\ForeignKey;
use core\database\pdo\parameter\PdoPrimitiveParam;
use core\database\pdo\PdoTable;
use core\database\query\Query;
use JsonSerializable;
use modules\forms\definition\FormDefinition;
use modules\forms\Form;

abstract class Entity implements JsonSerializable {
    public const ORIGIN_CODE = 'code';
    public const ORIGIN_DATABASE = 'database';
    public const VIRTUALITY_CHECK = 'defined-value'; // code smell



    private static array $schema;
    public static function addSchema(string $class, Schema $entity): void {
        self::$schema[$class] = $entity;
    }

    public static function getSchema(): ?Schema {
        return self::$schema[static::class] ?? null;
    }



    /**
     * Function that is called when class is loaded. Initialize table definition
     *
     * @return void
     */
    public static function init(): void {}

    public static function getIdColumn(): string {
        return static::getSchema()
            ->getTable()
            ->getIdColumn();
    }

    public static function getColumnEnumString(bool $includeIdColumn = true): string {
        $def = static::getSchema()->getTable();
        $table = '`'. $def->getTableName() .'`';
        $string = $includeIdColumn
            ? "$table.`". $def->getIdColumn() .'`'
            : "";

        $first = !$includeIdColumn;
        foreach ($def->getColumns() as $name => $definition) {
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
        $def = static::getSchema()->getTable();
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". $def->getTableName() . "`";
        return $def->getDatabase()
            ->fetch(Query::from($sql, $additional), static::class);
    }

    /**
     * @param string|Query|null $additional
     * @return array<static>|null
     */
    public static function fetchAll(string|Query|null $additional = null): ?array {
        $def = static::getSchema()->getTable();
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". $def->getTableName() ."`";
        return $def->getDatabase()
            ->fetchAll(Query::from($sql, $additional), static::class);
    }



    public static function initCreateForm(Form $form): void {
        static::getSchema()
            ->getForm()
            ->initForm($form, []);
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

        $def = static::getSchema()->getTable();
        $_id = new PdoPrimitiveParam($id);
        return $def->getDatabase()
            ->fetch(
                "SELECT ". static::getColumnEnumString() ." FROM `". $def->getTable()
                ."` WHERE `". $def->getIdColumn() ."` = $_id",
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
        $def = static::getSchema()->getTable();

        if (!isset($def->getColumns()[$name])) {
            return null;
        }

        $column = $def->getColumns()[$name];
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
        $columns = static::getSchema()
            ->getTable()
            ->getColumns();

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
        $columns = static::getSchema()
            ->getTable()
            ->getColumns();

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
        $columns = static::getSchema()
            ->getTable()
            ->getColumns();

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

        $def = static::getSchema()->getTable();
        $idColumn = $def->getIdColumn();
        $sql = "UPDATE `". $def->getTableName() ."` SET ";
        $params = [];
        $first = true;

        foreach (array_unique($this->updated) as $column) {
            if ($column === $idColumn) {
                continue;
            }

            if (!$first) {
                $sql .= ', ';
            }

            $sql .= "`$column` = ". StaticBuffer::PARAM_IDENT;
            $params[] = $this->data[$column];

            $first = false;
        }

        $params[] = $this->getId();
        $sql .= ' WHERE `'. $idColumn .'` = '. StaticBuffer::PARAM_IDENT;

        $def->getDatabase()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    protected function insert(): void {
        $def = static::getSchema()->getTable();

        $columns = [];
        $params = [];
        $values = "";
        $first = true;

        foreach ($def->getColumns() as $name => $definition) {
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

        $sql = 'INSERT INTO `'. $def->getTableName() ."` (". implode(', ', $columns) .") VALUES (". $values .")";
        $def->getDatabase()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    public function delete(): void {
        $def = static::getSchema()->getTable();
        $_id = new PdoPrimitiveParam($this->getId());
        $def->getDatabase()
            ->run("DELETE FROM `". $def->getTableName() ."` WHERE `". $def->getIdColumn() ."` = $_id");
    }

    public function getId(): int {
        if (isset($this->id)) {
            return $this->id;
        }

        return $this->id = $this->data[static::getSchema()->getTable()->getIdColumn()] ?? null;
    }

    public function jsonSerialize(): array {
        return $this->data;
    }

    public function getData(): array {
        return $this->data;
    }

    public function initUpdateForm(Form $form): void {
        static::getSchema()
            ->getForm()
            ->initForm($form, $this->data);
    }

    public function getExtension(string $class): ?Extension {
        $extension = static::getSchema()
            ->getExtension($class);

        $extension->setEntity($this);
        return $extension;
    }
}
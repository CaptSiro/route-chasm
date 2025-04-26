<?php

namespace core\database_v3\sql;

use core\database_v3\sql\query\Parameter;
use core\database_v3\sql\query\Query;
use core\database_v3\sql\query\SelectQuery;
use core\Identifier;
use Exception;
use JsonSerializable;
use ReflectionClass;

class Model implements JsonSerializable, Identifier {
    private static array $descriptions = [];


    public static function getDescription(string $class): ModelDescription {
        if (isset(self::$descriptions[$class])) {
            return self::$descriptions[$class];
        }

        $reflection = new ReflectionClass($class);
        $tables = $reflection->getAttributes(Table::class);
        if (empty($tables)) {
            throw new Exception("Model must have Table attribute");
        }

        $databases = $reflection->getAttributes(Database::class);
        $database = empty($databases)
            ? new Database()
            : $databases[0]->newInstance();

        $idColumn = null;
        $columns = [];
        $alias = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var Column $column */
            $column = $attributes[0]->newInstance();
            $description = new ColumnDescription(
                $property->getName(),
                $column->name,
                $column->type
            );

            $columns[] = $description;
            $alias[$description->alias] = $description;

            if ($column->primaryKey) {
                if (!is_null($idColumn)) {
                    throw new Exception("Only one primary key column is allowed for model '$class'");
                }

                $idColumn = $description;
            }
        }

        if (is_null($idColumn)) {
            throw new Exception("No primary key found for model '$class'");
        }

        return self::$descriptions[$class] = new ModelDescription(
            $tables[0]->newInstance()->name,
            $database->getConnection(),
            $idColumn,
            $columns,
            $alias
        );
    }

    public static function getTable(): string {
        return static::getDescription(static::class)->table;
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

    public static function fromRecord(?array $record, Origin $origin = Origin::EXTERNAL): ?static {
        if (is_null($record)) {
            return null;
        }

        $description = static::getDescription(static::class);
        $instance = new static();

        foreach ($description->columns as $column) {
            if (!isset($record[$column->name])) {
                continue;
            }

            $instance->{$column->alias} = $record[$column->name];
        }

        $instance->origin = $origin;
        return $instance;
    }

    /**
     * @param array<array> $records
     * @param Origin $origin
     * @return array<static>
     */
    public static function fromRecords(array $records, Origin $origin = Origin::EXTERNAL): array {
        foreach ($records as $i => $record) {
            $records[$i] = static::fromRecord($record, $origin);
        }

        return $records;
    }

    protected static function addProjection(ModelDescription $description, SelectQuery $sql, ?array $projection = null): void {
        if (is_null($projection)) {
            foreach ($description->columns as $column) {
                $sql->projection($column->name);
            }

            return;
        }

        foreach ($description->columns as $column) {
            if (in_array($column->name, $projection)) {
                $sql->projection($column->name);
            }
        }
    }

    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return ?static
     */
    public static function first(?array $projection = null, Query|string|null $where = null): ?static {
        $description = static::getDescription(static::class);

        $sql = Sql::select($description->getEscapedTable());
        static::addProjection($description, $sql, $projection);

        if (!is_null($where)) {
            $sql->where($where);
        }

        $sql->limit(1);

        $record = $description
            ->connection
            ->fetch($sql->toQuery($description->connection));

        return static::fromRecord($record);
    }

    /**
     * @param mixed $id
     * @param array|null $projection
     * @return static|null
     */
    public static function fromId(mixed $id, ?array $projection = null): ?static {
        $description = static::getDescription(static::class);
        $idColumnName = $description->getEscapedIdColumnName();

        return self::first($projection, new Query(
             "$idColumnName = ?",
            [Parameter::infer($id)]
        ));
    }

    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return array<static>
     */
    public static function all(?array $projection = null, Query|string|null $where = null): array {
        $description = static::getDescription(static::class);
        $sql = Sql::select($description->getEscapedTable());

        static::addProjection($description, $sql, $projection);

        if (!is_null($where)) {
            $sql->where($where);
        }

        return self::fromRecords(
            $description
                ->connection
                ->fetchAll($sql->toQuery($description->connection))
        );
    }



    private Origin $origin;
    private array $updated = [];

    public function __construct() {
        $this->origin = Origin::APPLICATION;
    }



    public function __get(string $name) {
        if (!isset($this->$name)) {
            return null;
        }

        return $this->$name;
    }

    public function getId(): mixed {
        $description = static::getDescription(static::class);
        return $this->{$description->idColumn->alias};
    }

    public function __set(string $alias, $value): void {
        $this->updated[$alias] = 0;
        $this->$alias = $value;
    }

    public function set(array $data): static {
        $description = static::getDescription(static::class);

        foreach ($data as $property => $value) {
            if (!isset($description->alias[$property])) {
                continue;
            }

            $this->__set($property, $value);
        }

        return $this;
    }

    private function insert(): Action {
        $description = static::getDescription(static::class);
        $sql = Sql::insert($description->table);

        $sql->columns(array_keys($this->updated));
        $record = [];

        foreach ($this->updated as $alias => $ignored) {
            $column = $description->alias[$alias];
            $record[] = new Parameter($this->{$column->alias}, $column->type);
        }

        $sql->value($record);
        $sideEffect = $description
            ->connection
            ->run($sql->toQuery($description->connection));

        if ($sideEffect->rowsAffected === 0) {
            return Action::NONE;
        }

        $this->{$description->idColumn->alias} = $sideEffect->lastInsertedId;
        return Action::INSERT;
    }

    public function save(): Action {
        if ($this->origin === Origin::APPLICATION) {
            return $this->insert();
        }

        $description = static::getDescription(static::class);
        $sql = Sql::update($description->table);
        $idColumnName = $description->idColumn->name;

        $setClauses = 0;

        foreach ($this->updated as $alias => $ignored) {
            $column = $description->alias[$alias];
            if ($column->name === $idColumnName) {
                continue;
            }

            $sql->set($column->name, new Parameter($this->{$column->alias}, $column->type));
            $setClauses++;
        }

        if ($setClauses === 0) {
            return Action::NONE;
        }

        $sql->where(new Query(
            $description->getEscapedIdColumnName() ." = ?",
            [new Parameter($this->{$description->idColumn->alias}, $description->idColumn->type)]
        ));

        $sideEffect = $description
            ->connection
            ->run($sql->toQuery($description->connection));

        if ($sideEffect->rowsAffected === 0) {
            return Action::NONE;
        }

        return Action::UPDATE;
    }

    public function delete(): Action {
        $description = static::getDescription(static::class);
        $idColumnName = $description->getEscapedIdColumnName();

        $sql = Sql::delete($description->table)
            ->where(new Query(
                "$idColumnName = ?",
                [new Parameter($this->{$description->idColumn->alias}, $description->idColumn->type)]
            ))
            ->limit(1);

        $sideEffect = $description
            ->connection
            ->run($sql->toQuery($description->connection));

        if ($sideEffect->rowsAffected === 0) {
            return Action::NONE;
        }

        return Action::DELETE;
    }

    public function getOrigin(): Origin {
        return $this->origin;
    }

    public function jsonSerialize(): object {
        return $this;
    }

    public function getData(): array {
        $description = static::getDescription(static::class);
        $data = [];

        foreach ($description->alias as $alias => $ignored) {
            $data[$alias] = $this->$alias;
        }

        return $data;
    }
}
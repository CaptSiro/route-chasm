<?php

namespace core\database_v3\sql;

use core\database_v3\sql\query\Parameter;
use core\database_v3\sql\query\Query;
use core\Identifier;
use core\view\View;
use JsonSerializable;

class Model implements JsonSerializable, Identifier {
    public static function getTable(): string {
        return ModelDescription::extract(static::class)->table;
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
        return ModelFactory::extract(static::class)
            ->fromRecord($record, $origin);
    }

    /**
     * @param array<array> $records
     * @param Origin $origin
     * @return array<static>
     */
    public static function fromRecords(array $records, Origin $origin = Origin::EXTERNAL): array {
        return ModelFactory::extract(static::class)
            ->fromRecords($records, $origin);
    }


    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return ?static
     */
    public static function first(?array $projection = null, Query|string|null $where = null): ?static {
        return ModelFactory::extract(static::class)
            ->first($projection, $where);
    }

    /**
     * @param mixed $id
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @return static|null
     */
    public static function fromId(mixed $id, ?array $projection = null): ?static {
        return ModelFactory::extract(static::class)
            ->first($id, $projection);
    }

    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return array<static>
     */
    public static function all(?array $projection = null, Query|string|null $where = null): array {
        return ModelFactory::extract(static::class)
            ->all($projection, $where);
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
        $description = ModelDescription::extract(static::class);
        return $this->{$description->idColumn->alias};
    }

    public function __set(string $alias, $value): void {
        $this->updated[$alias] = 0;
        $this->$alias = $value;
    }

    public function set(array $data): static {
        $description = ModelDescription::extract(static::class);

        foreach ($data as $property => $value) {
            $column = $description->alias[$property];
            if (!isset($column)) {
                continue;
            }

            $this->__set($property, $column->transform($value));
        }

        return $this;
    }

    public function setOrigin(Origin $origin): void {
        $this->origin = $origin;
    }

    private function insert(): Action {
        $description = ModelDescription::extract(static::class);
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

    public function save(): Action|View {
        if ($this->origin === Origin::APPLICATION) {
            return $this->insert();
        }

        $description = ModelDescription::extract(static::class);
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
        $description = ModelDescription::extract(static::class);
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
        $description = ModelDescription::extract(static::class);
        $data = [];

        foreach ($description->alias as $alias => $ignored) {
            $data[$alias] = $this->$alias;
        }

        return $data;
    }
}
<?php

namespace core\database\sql;

use components\core\Admin\Nexus\NexusProxyItem;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SqlQuery;
use core\Identifier;
use core\view\View;
use JsonSerializable;

class Model implements JsonSerializable, Identifier, NexusProxyItem {
    public static function get(?Model $model, string $property, mixed $or = null): mixed {
        if (is_null($model)) {
            return $or;
        }

        return $model->$property;
    }

    public static function getString(?Model $model, string $property): string {
        return self::get($model, $property, '');
    }

    public static function getDescription(): ModelDescription {
        return ModelDescription::extract(static::class);
    }

    public static function getTable(): string {
        return ModelDescription::extract(static::class)->table;
    }



    /**
     * @param array<string, mixed> $properties
     * @param bool $save
     * @return static
     */
    public static function create(array $properties, bool $save = true): static {
        $instance = new static();
        $instance->set($properties);

        if ($save) {
            $instance->save();
        }

        return $instance;
    }

    /**
     * @param Model|null $instance
     * @param array<string, mixed> $properties
     * @param bool $create
     * @param bool $save
     * @return static|null
     */
    protected static function createConditionally(
        ?self $instance,
        array $properties,
        bool $create = false,
        bool $save = true
    ): ?static {
        if (!is_null($instance)) {
            return $instance;
        }

        if ($create) {
            return self::create($properties, $save);
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
            ->fromId($id, $projection);
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



    private Origin $_origin;
    private array $_updated = [];
    private bool $_unsafeAccess = false;

    public function __construct() {
        $this->_origin = Origin::APPLICATION;
    }



    public function useUnsafeAccess(bool $access): void {
        $this->_unsafeAccess = $access;
    }

    public function getUpdatedUnsafe(): array {
        $ret = [];

        foreach ($this->_updated as $property => $_) {
            $ret[$property] = $this->$property;
        }

        return $ret;
    }

    public function __get(string $name) {
        if (!isset($this->$name)) {
            return null;
        }

        return $this->$name;
    }

    public function getId(): mixed {
        $description = ModelDescription::extract(static::class);
        $id = $description->idColumn->alias;
        if (!isset($this->{$id})) {
            return null;
        }

        return $this->{$id};
    }

    public function __set(string $alias, $value): void {
        if (!$this->_unsafeAccess) {
            $this->_updated[$alias] = 0;
        }

        $this->$alias = $value;
    }

    public function set(array $data): static {
        $description = ModelDescription::extract(static::class);

        foreach ($data as $property => $value) {
            if (!isset($description->alias[$property])) {
                continue;
            }

            $column = $description->alias[$property];
            if (!isset($column)) {
                continue;
            }

            $this->__set($property, $column->transform($value));
        }

        return $this;
    }

    public function setOrigin(Origin $_origin): void {
        $this->_origin = $_origin;
    }

    public function notSavable(): void {
        $this->_origin = Origin::UNKNOWN;
    }

    public function isNewRecord(): bool {
        return $this->_origin === Origin::APPLICATION;
    }

    public function isSavable(): bool {
        return $this->_origin !== Origin::UNKNOWN;
    }

    public function isEditable(): bool {
        return true;
    }

    public function isDeletable(): bool {
        return true;
    }

    private function insertQuery(): SqlQuery {
        $description = ModelDescription::extract(static::class);
        $sql = Sql::insert($description->table);

        $properties = empty($this->_updated)
            ? $description->alias
            : $this->_updated;

        $columns = [];
        $record = [];

        foreach ($properties as $alias => $ignored) {
            $column = $description->alias[$alias];
            $columns[] = $column->name;
            $record[] = new Parameter($this->{$column->alias}, $column->type);
        }

        $sql->columns($columns);
        $sql->value($record);

        return $sql;
    }

    private function updateQuery(): ?SqlQuery {
        $description = ModelDescription::extract(static::class);
        $sql = Sql::update($description->table);
        $idColumnName = $description->idColumn->name;

        $setClauses = 0;

        foreach ($this->_updated as $alias => $ignored) {
            $column = $description->alias[$alias];
            if ($column->name === $idColumnName) {
                continue;
            }

            $sql->set($column->name, new Parameter($this->{$column->alias}, $column->type));
            $setClauses++;
        }

        if ($setClauses === 0) {
            return null;
        }

        $sql->where(new Query(
            $description->getEscapedIdColumnName() ." = ?",
            [new Parameter($this->{$description->idColumn->alias}, $description->idColumn->type)]
        ));

        return $sql;
    }

    public function deleteQuery(): SqlQuery {
        $description = ModelDescription::extract(static::class);
        $idColumnName = $description->getEscapedIdColumnName();

        return Sql::delete($description->table)
            ->where(new Query(
                "$idColumnName = ?",
                [new Parameter($this->{$description->idColumn->alias}, $description->idColumn->type)]
            ))
            ->limit(1);
    }

    public function saveQuery(): ?SqlQuery {
        if (!$this->isSavable()) {
            return null;
        }

        if ($this->isNewRecord()) {
            return $this->insertQuery();
        }

        return $this->updateQuery();
    }

    private function insert(): DatabaseAction|View {
        $description = ModelDescription::extract(static::class);
        $sideEffect = $this->insertQuery()->run($description->connection);
        if ($sideEffect->rowsAffected === 0) {
            return DatabaseAction::NONE;
        }

        $this->{$description->idColumn->alias} = $sideEffect->lastInsertedId;
        return DatabaseAction::INSERT;
    }

    public function save(): DatabaseAction|View {
        if (!$this->isSavable()) {
            return DatabaseAction::NONE;
        }

        if ($this->isNewRecord()) {
            return $this->insert();
        }

        $description = ModelDescription::extract(static::class);
        $sql = $this->updateQuery();
        if (is_null($sql)) {
            return DatabaseAction::NONE;
        }

        $sideEffect = $sql->run($description->connection);
        if ($sideEffect->rowsAffected === 0) {
            return DatabaseAction::NONE;
        }

        return DatabaseAction::UPDATE;
    }

    public function delete(): DatabaseAction {
        $description = ModelDescription::extract(static::class);
        $sideEffect = $this->deleteQuery()->run($description->connection);
        if ($sideEffect->rowsAffected === 0) {
            return DatabaseAction::NONE;
        }

        return DatabaseAction::DELETE;
    }

    public function getOrigin(): Origin {
        return $this->_origin;
    }

    public function jsonSerialize(): object {
        return $this;
    }

    public function getData(): array {
        $description = ModelDescription::extract(static::class);
        $data = [];

        foreach ($description->alias as $alias => $ignored) {
            $data[$alias] = $this->$alias ?? null;
        }

        return $data;
    }
}
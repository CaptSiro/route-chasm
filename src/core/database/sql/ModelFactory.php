<?php

namespace core\database\sql;

use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;

class ModelFactory {
    private static array $factories = [];

    public static function extract(string $modelClass): ModelFactory {
        if (isset(self::$factories[$modelClass])) {
            return self::$factories[$modelClass];
        }

        return self::$factories[$modelClass] = new static($modelClass);
    }



    public function __construct(
        protected string $modelClass
    ) {}



    public function getDescription(): ModelDescription {
        return ModelDescription::extract($this->modelClass);
    }

    public function new(): Model {
        return new $this->modelClass();
    }

    public function fromRecord(?array $record, Origin $origin = Origin::EXTERNAL): ?Model {
        if (is_null($record)) {
            return null;
        }

        $description = ModelDescription::extract($this->modelClass);
        $instance = $this->new();

        foreach ($description->columns as $column) {
            if (!isset($record[$column->name])) {
                continue;
            }

            $instance->{$column->alias} = $record[$column->name];
        }

        $instance->setOrigin($origin);
        return $instance;
    }

    /**
     * @param array $records
     * @param Origin $origin
     * @return array<Model>
     */
    public function fromRecords(array $records, Origin $origin = Origin::EXTERNAL): array {
        foreach ($records as $i => $record) {
            $records[$i] = $this->fromRecord($record, $origin);
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


    public function first(?array $projection = null, Query|string|null $where = null): ?Model {
        $description = ModelDescription::extract($this->modelClass);

        $sql = Sql::select($description->getEscapedTable());
        static::addProjection($description, $sql, $projection);

        if (!is_null($where)) {
            $sql->where($where);
        }

        $sql->limit(1);

        $record = $description
            ->connection
            ->fetch($sql->toQuery($description->connection));

        return $this->fromRecord($record);
    }

    public function fromId(mixed $id, ?array $projection = null): ?Model {
        $description = ModelDescription::extract($this->modelClass);
        $idColumnName = $description->getEscapedIdColumnName();

        return self::first($projection, new Query(
            "$idColumnName = ?",
            [Parameter::infer($id)]
        ));
    }

    /**
     * @param array|null $projection
     * @param Query|string|null $where
     * @return array<Model>
     */
    public function all(?array $projection = null, Query|string|null $where = null): array {
        $description = ModelDescription::extract($this->modelClass);
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
}
<?php

namespace core\database\sql;

use components\core\CallStack\CallStack;
use components\core\Terminal\Terminal;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\query\SqlQuery;

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

    public function finish(Model $model): Model {
        $model->useUnsafeAccess(false);
        return $model;
    }

    public function fromRecord(?array $record, Origin $origin = Origin::EXTERNAL): ?Model {
        if (is_null($record)) {
            return null;
        }

        $description = ModelDescription::extract($this->modelClass);
        $instance = $this->new();
        $instance->useUnsafeAccess(true);

        foreach ($description->columns as $column) {
            if (!$column->nullable && !isset($record[$column->name])) {
                continue;
            }

            $instance->{$column->alias} = $record[$column->name] ?? null;
        }

        $instance->setOrigin($origin);
        return $this->finish($instance);
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
        $driver = $description->connection->getDriver();
        if (is_null($projection)) {
            foreach ($description->columns as $column) {
                $sql->projection($driver->escapeColumn($column->name));
            }

            return;
        }

        foreach ($description->columns as $column) {
            if (in_array($column->name, $projection)) {
                $sql->projection($driver->escapeColumn($column->name));
            }
        }
    }


    public function firstQuery(?array $projection = null, Query|string|null $where = null): SqlQuery {
        $description = ModelDescription::extract($this->modelClass);

        $sql = Sql::select($description->getEscapedTable());
        static::addProjection($description, $sql, $projection);

        if (!is_null($where)) {
            $sql->where($where);
        }

        $sql->limit(1);
        return $sql;
    }

    public function first(?array $projection = null, Query|string|null $where = null): ?Model {
        $description = ModelDescription::extract($this->modelClass);
        $record = $this
            ->firstQuery($projection, $where)
            ->fetch($description->connection);

        return $this->fromRecord($record);
    }

    public function fromId(mixed $id, ?array $projection = null): ?Model {
        $description = ModelDescription::extract($this->modelClass);
        $idColumnName = $description->getEscapedIdColumnName();

        return self::first(
            $projection,
            where: Query::infer("$idColumnName = ?", $id)
        );
    }

    public function allQuery(?array $projection = null, Query|string|null $where = null): SqlQuery {
        $description = ModelDescription::extract($this->modelClass);
        $sql = Sql::select($description->getEscapedTable());

        static::addProjection($description, $sql, $projection);

        if (!is_null($where)) {
            $sql->where($where);
        }

        return $sql;
    }

    /**
     * @param array|null $projection
     * @param Query|string|null $where
     * @return array<Model>
     */
    public function all(?array $projection = null, Query|string|null $where = null): array {
        $description = ModelDescription::extract($this->modelClass);
        $records = $this
            ->allQuery($projection, $where)
            ->fetchAll($description->connection);

        return self::fromRecords($records);
    }
}
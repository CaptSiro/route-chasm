<?php

namespace core\database\sql;

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

        foreach ($description->getColumns() as $column) {
            if (!$column->isNullable() && !isset($record[$column->getName()])) {
                continue;
            }

            $instance->{$column->getAlias()} = $record[$column->getName()] ?? null;
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
        $driver = $description->getConnection()->getDriver();
        if (is_null($projection)) {
            foreach ($description->getColumns() as $column) {
                $sql->projection($driver->escapeColumn($column->getName()));
            }

            return;
        }

        foreach ($description->getColumns() as $column) {
            if (in_array($column->getName(), $projection)) {
                $sql->projection($driver->escapeColumn($column->getName()));
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

    public function firstExecute(SqlQuery $query): ?Model {
        return $this->fromRecord(
            $query->fetch(
                ModelDescription::extract($this->modelClass)->getConnection()
            )
        );
    }

    public function first(?array $projection = null, Query|string|null $where = null): ?Model {
        return $this->firstExecute(
            $this->firstQuery($projection, $where)
        );
    }

    public function fromIdQuery(mixed $id, ?array $projection = null): SqlQuery {
        $description = ModelDescription::extract($this->modelClass);
        $idColumnName = $description->getEscapedIdColumnName();

        return $this->firstQuery(
            $projection,
            where: Query::infer("$idColumnName = ?", $id)
        );
    }

    public function fromId(mixed $id, ?array $projection = null): ?Model {
        return $this->firstExecute(
            $this->fromIdQuery($id, $projection)
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
     * @return array<Model>
     */
    public function allExecute(SqlQuery $query): array {
        return self::fromRecords(
            $query->fetchAll(
                ModelDescription::extract($this->modelClass)->getConnection()
            )
        );
    }

    /**
     * @param array|null $projection
     * @param Query|string|null $where
     * @return array<Model>
     */
    public function all(?array $projection = null, Query|string|null $where = null): array {
        return $this->allExecute(
            $this->allQuery($projection, $where)
        );
    }
}
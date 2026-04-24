<?php

namespace core\database\sql;

use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\query\SqlQuery;

class ModelFactory {
    public const PROJECTION_COUNT = 'COUNT(*) as n';
    public const PROJECTION_COUNT_COLUMN_NAME = 'n';



    private static array $factories = [];

    public static function extract(string $modelClass): ModelFactory {
        if (isset(self::$factories[$modelClass])) {
            return self::$factories[$modelClass];
        }

        return self::$factories[$modelClass] = new static($modelClass);
    }

    public static function countExecuteConnection(SqlQuery $query, Connection $connection): int {
        $result = $connection->fetch(
            $query->toQuery($connection)
        );

        if (is_null($result)) {
            return -1;
        }

        return intval($result[self::PROJECTION_COUNT_COLUMN_NAME]);
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

        foreach ($description->getColumns() as $column) {
            if (!$column->isNullable() && !isset($record[$column->getName()])) {
                continue;
            }

            $instance->{$column->getAlias()} = $record[$column->getName()] ?? null;
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

    public function addProjection(SelectQuery $sql, ?array $projection = null): void {
        $description = ModelDescription::extract($this->modelClass);
        $driver = $description->getConnection()->getDriver();

        if (is_null($projection)) {
            foreach ($description->getColumns() as $column) {
                $sql->projection($driver->escapeColumn($column->getName()));
            }

            return;
        }

        foreach ($projection as $column) {
            $sql->projection($column);
        }
    }

    public function firstQuery(?array $projection = null, Query|string|null $where = null): SelectQuery {
        $description = ModelDescription::extract($this->modelClass);

        $sql = Sql::select($description->getEscapedTable());
        $this->addProjection($sql, $projection);

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

    public function countProjection(): string {
        return self::PROJECTION_COUNT;
    }

    public function countQuery(Query|string|null $where = null): SelectQuery {
        return $this->firstQuery([self::PROJECTION_COUNT], $where);
    }

    public function countExecute(SqlQuery $query): int {
        return static::countExecuteConnection($query, $this->getDescription()
            ->getConnection());
    }

    public function count(Query|string|null $where = null): int {
        return $this->countExecute(
            $this->countQuery($where)
        );
    }

    public function fromIdQuery(mixed $id, ?array $projection = null): SelectQuery {
        $idColumnName = $this->getDescription()
            ->getEscapedIdColumnName();

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

    public function allQuery(?array $projection = null, Query|string|null $where = null): SelectQuery {
        $description = $this->getDescription();
        $sql = Sql::select($description->getEscapedTable());

        $this->addProjection($sql, $projection);

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
                $this->getDescription()
                    ->getConnection()
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



    public function randomQuery(Query|string|null $where = null): SelectQuery {
        return $this->firstQuery($where)
            ->order("RAND()");
    }

    public function random(Query|string|null $where = null): ?Model {
        return $this->firstExecute(
            $this->randomQuery($where)
        );
    }
}
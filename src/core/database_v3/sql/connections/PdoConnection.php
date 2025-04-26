<?php

namespace core\database_v3\sql\connections;

use core\database_v3\sql\Connection;
use core\database_v3\sql\Driver;
use core\database_v3\sql\Origin;
use core\database_v3\sql\query\Query;
use core\database_v3\sql\Record;
use core\database_v3\sql\SideEffect;
use PDO;
use PDOStatement;

class PdoConnection implements Connection {
    public const TYPES = [
        "boolean" => PDO::PARAM_BOOL,
        "integer" => PDO::PARAM_INT,
        "double" => PDO::PARAM_STR,
        "float" => PDO::PARAM_STR,
        "string" => PDO::PARAM_STR,
        "NULL" => PDO::PARAM_NULL,
    ];



    public function __construct(
        protected PDO $connection,
        protected Driver $driver
    ) {}



    public function getDriver(): Driver {
        return $this->driver;
    }

    protected function createStatement(Query $query): PDOStatement {
        if (empty($query->parameters)) {
            return $this->connection->query($query->sql);
        }

        $statement = $this->connection->prepare($query->sql);

        foreach ($query->parameters as $i => $parameter) {
            $param = gettype($i) === "integer"
                ? $i + 1
                : $i;

            $statement->bindValue($param, $parameter->value, self::TYPES[$parameter->type]);
        }

        return $statement;
    }

    protected function setFetchMode(PDOStatement $statement, ?string $class): void {
        if (is_null($class)) {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            return;
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
    }

    protected function setExternalOrigin(array|Record $record): void {
        if ($record instanceof Record) {
            $record->setOrigin(Origin::EXTERNAL);
        }
    }

    public function fetch(Query $query, ?string $class = null): ?array  {
        $statement = $this->createStatement($query);
        $this->setFetchMode($statement, $class);

        $statement->execute();
        $record = $statement->fetch();

        if ($record === false) {
            return null;
        }

        $this->setExternalOrigin($record);
        return $record;
    }

    public function fetchAll(Query $query, ?string $class = null): array {
        $statement = $this->createStatement($query);
        $this->setFetchMode($statement, $class);

        $statement->execute();
        $records = $statement->fetchAll();

        if ($records === false) {
            return [];
        }

        foreach ($records as $record) {
            $this->setExternalOrigin($record);
        }

        return $records;
    }

    public function run(Query $query): SideEffect {
        $statement = $this->createStatement($query);

        $statement->execute();

        return new SideEffect(
            $this->connection->lastInsertId(),
            $statement->rowCount()
        );
    }
}
<?php

namespace core\database\sql\connections;

use core\database\sql\Connection;
use core\database\sql\Driver;
use core\database\sql\Origin;
use core\database\sql\query\Query;
use core\database\sql\Record;
use core\database\sql\SideEffect;
use PDO;
use PDOException;
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
        if (empty($query->getParameters())) {
            try {
                return $this->connection->query($query->getSql());
            } catch (PDOException $exception) {
                $sql = $query->getSql();
                throw new PDOException($exception->getMessage() ." SQL: '$sql'");
            }
        }

        $statement = $this->connection->prepare($query->getSql());

        foreach ($query->getParameters() as $i => $parameter) {
            $param = gettype($i) === "integer"
                ? $i + 1
                : $i;

            if (!isset(self::TYPES[$parameter->getType()])) {
                var_dump($query);
                exit;
            }

            $statement->bindValue($param, $parameter->getValue(), self::TYPES[$parameter->getType()]);
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

        try {
            $statement->execute();
        } catch (PDOException $exception) {
            $sql = $query->getSql();
            throw new PDOException($exception->getMessage() ." SQL: '$sql'");
        }

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

        try {
            $statement->execute();
        } catch (PDOException $exception) {
            $sql = $query->getSql();
            throw new PDOException($exception->getMessage() ." SQL: '$sql'");
        }

        return new SideEffect(
            $this->connection->lastInsertId(),
            $statement->rowCount()
        );
    }
}
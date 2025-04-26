<?php

namespace core\database_v3\sql;

use core\database_v3\sql\query\DeleteQuery;
use core\database_v3\sql\query\InsertQuery;
use core\database_v3\sql\query\SelectQuery;
use core\database_v3\sql\query\UpdateQuery;

class Sql {
    /**
     * @var array<string, Connection>
     */
    private static array $connections;

    public static function connect(string $name, Driver $driver): void {
        static::$connections[$name] = $driver->connect();
    }

    public static function getConnection(?string $name = null): Connection {
        $name ??= array_keys(static::$connections)[0];
        return static::$connections[$name];
    }

    public static function select(string $table): SelectQuery {
        return (new SelectQuery())
            ->from($table);
    }

    public static function update(string $table): UpdateQuery {
        return new UpdateQuery($table);
    }

    public static function insert(string $table): InsertQuery {
        return new InsertQuery($table);
    }

    public static function delete(string $table): DeleteQuery {
        return new DeleteQuery($table);
    }
}
<?php

namespace core\database\sql;

use core\database\sql\query\DeleteQuery;
use core\database\sql\query\InsertQuery;
use core\database\sql\query\SelectQuery;
use core\database\sql\query\UpdateQuery;

class Sql {
    /**
     * @var array<string, Connection>
     */
    private static array $connections;

    public static function dateNow(): string {
        return date('Y-m-d');
    }

    public static function datetimeNow(): string {
        return date('Y-m-d H:i:s');
    }

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
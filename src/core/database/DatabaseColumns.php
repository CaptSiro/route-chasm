<?php

namespace core\database;

trait DatabaseColumns {
    protected static array $columns;

    public static function getColumns(): array {
        return self::$columns;
    }
}
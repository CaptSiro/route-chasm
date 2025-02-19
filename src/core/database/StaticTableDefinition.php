<?php

namespace core\database;

trait StaticTableDefinition {
    protected static TableDefinition $table;

    public static function getTableDefinition(): TableDefinition {
        return self::$table;
    }
}
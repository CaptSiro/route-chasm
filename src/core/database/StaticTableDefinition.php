<?php

namespace core\database;

trait StaticTableDefinition {
    protected static TableDefinition $definition;

    public static function getTableDefinition(): TableDefinition {
        return self::$definition;
    }
}
<?php

namespace core\database\sql;

trait ModelCache {
    private static array $modelCache = [];

    protected static function modelCache_get(string $key, ?self $or = null): ?static {
        return static::$modelCache[$key] ?? $or;
    }

    protected static function modelCache_set(string $key, ?self $value): ?static {
        if (is_null($value)) {
            return null;
        }

        return static::$modelCache[$key] = $value;
    }
}
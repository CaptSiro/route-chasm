<?php

namespace core\database\sql;

use Closure;

trait ModelCache {
    private static bool $isModelCacheLoaded = false;
    /**
     * @var array<static>
     */
    private static array $modelCache = [];
    /**
     * @var array<int, static>
     */
    private static array $modelCacheId = [];



    protected static function modelCache_fromId(int $id): ?static {
        return static::$modelCacheId[$id];
    }

    protected static function modelCache_get(string $key, ?self $or = null): ?static {
        return static::$modelCache[$key] ?? $or;
    }

    protected static function modelCache_set(string $key, ?self $value): ?static {
        if (is_null($value)) {
            return null;
        }

        static::$modelCacheId[$value->getId()] = $value;
        return static::$modelCache[$key] = $value;
    }

    protected static function modelCache_loadAll(Closure $keyGenerator): void {
        if (static::$isModelCacheLoaded) {
            return;
        }

        foreach (static::all() as $record) {
            static::modelCache_set(($keyGenerator)($record), $record);
        }

        static::$isModelCacheLoaded = true;
    }
}
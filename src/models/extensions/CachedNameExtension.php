<?php

namespace models\extensions;

use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use models\core\Navigation\NavigationFactoryRecord;

trait CachedNameExtension {
    use ModelCache, NameExtension;

    public static function fromName(string $name): ?static {
        self::modelCache_loadAll(
            fn(NavigationFactoryRecord $x) => $x->name
        );

        $record = static::modelCache_get($name);

        if (is_null($record)) {
            static::modelCache_set($name, $record = static::first(
                where: Query::infer('name = ?', $name)
            ));
        }

        return $record;
    }
}
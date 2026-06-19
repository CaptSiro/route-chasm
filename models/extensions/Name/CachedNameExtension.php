<?php

namespace models\extensions\Name;

use core\database\sql\ModelCache;
use core\database\sql\query\Query;

trait CachedNameExtension {
    use ModelCache, NameExtension;

    public static function fromName(string $name): ?static {
        self::modelCache_loadAll(
            fn(Name $x) => $x->getName()
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
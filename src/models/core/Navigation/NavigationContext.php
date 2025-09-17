<?php

namespace models\core\Navigation;

use core\App;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Name\CachedNameExtension;
use models\extensions\Name\Name;

#[Table('core_navigation_context')]
#[Database(App::DATABASE)]
class NavigationContext extends Model implements Name {
    use CachedNameExtension;

    public const DEFAULT_CONTEXT_ID = 1;

    public static function getContextId(?string $contextName): int {
        if (is_null($contextName)) {
            return self::DEFAULT_CONTEXT_ID;
        }

        return self::fromName($contextName)?->getId()
            ?? self::DEFAULT_CONTEXT_ID;
    }
}
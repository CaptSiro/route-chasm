<?php

namespace models\core\Navigation;

use core\App;
use core\database\sql\Column;
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

        return self::fromName($contextName)?->id
            ?? self::DEFAULT_CONTEXT_ID;
    }



    #[Column('id_navigation_context', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;
}
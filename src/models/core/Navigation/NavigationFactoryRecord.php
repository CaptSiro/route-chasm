<?php

namespace models\core\Navigation;

use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\Table;
use core\forms\description\TextField;

#[Table('core_navigation_factory')]
#[Database(App::DATABASE)]
class NavigationFactoryRecord extends Model {
    use ModelCache;

    public static function fromName(string $name, bool $create = false): ?static {
        self::modelCache_loadAll(
            fn(NavigationFactoryRecord $x) => $x->name
        );

        return self::createConditionally(
            self::modelCache_get($name),
            ['name' => $name],
            $create,
        );
    }

    public static function allCached(): array {
        self::modelCache_loadAll(
            fn(NavigationFactoryRecord $x) => $x->name
        );

        return self::$modelCacheId;
    }



    #[Column('id_navigation_factory', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;



    // Model
    public function getHumanIdentifier(): string {
        return $this->name;
    }
}
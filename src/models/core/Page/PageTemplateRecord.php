<?php

namespace models\core\Page;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Name\CachedNameExtension;
use models\extensions\Name\Name;

#[Table('core_page_template')]
#[Database(App::DATABASE)]
class PageTemplateRecord extends Model implements Name {
    public static function fromNameCreate(string $name, bool $create = false): ?static {
        return self::createConditionally(
            self::fromName($name),
            ['name' => $name],
            $create
        );
    }



    use CachedNameExtension;

    #[Column('id_page_template', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;
}
<?php

namespace models\core\Page;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\CachedNameExtension;

#[Table('core_page_template')]
#[Database(App::DATABASE)]
class PageTemplate extends Model {
    use CachedNameExtension;

    #[Column('id_page_template', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;
}
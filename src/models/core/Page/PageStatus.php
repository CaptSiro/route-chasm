<?php

namespace models\core\Page;

use components\layout\Grid\description\Grid;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\CachedNameExtension;
use models\extensions\Editable\EditableExtension;

#[Grid]
#[Table('core_page_status')]
#[Database(App::DATABASE)]
class PageStatus extends Model {
    use EditableExtension, CachedNameExtension;

    #[Column('id_page_status', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;
}
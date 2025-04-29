<?php

namespace models\core;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use modules\forms\description\TextField;

#[Grid]
#[Table('core_resource')]
#[Database(App::DATABASE)]
class Resource extends Model {
    #[Column(type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;
}
<?php

namespace models\core\Page;

use components\layout\Grid\description\Grid;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Editable\EditableExtension;
use models\extensions\Name\CachedNameExtension;
use models\extensions\Name\Name;

#[Grid]
#[Table('core_page_status')]
#[Database(App::DATABASE)]
class PageStatus extends Model implements Name {
    public const ID_DRAFT = 1;
    public const ID_PUBLIC = 2;
    public const ID_ARCHIVED = 3;



    use EditableExtension, CachedNameExtension;

    #[Column('id_page_status', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;



    public function is(int $id): bool {
        return $this->id === $id;
    }
}
<?php

namespace models\docs;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Name\Name;
use models\extensions\Name\NameExtension;

#[Database(App::DATABASE)]
#[Table('docs_fragment')]
class Fragment extends Model implements Name {
    use NameExtension;

    #[Column('id_fragment', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column(type: Column::TYPE_STRING)]
    public string $summary;
}
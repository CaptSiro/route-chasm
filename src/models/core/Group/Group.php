<?php

namespace models\core\Group;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;
use modules\forms\description\TextField;

/**
 * @property int $id
 * @property string $name
 */

#[Grid(new GroupProxy())]
#[Table('core_group')]
#[Database(App::DATABASE)]
class Group extends Model implements Editable {
    use EditableExtension;

    public const DEFAULT = 'Default';
    public const ADMIN = 'Admin';
    public const ROOT = 'Root';
    public const TABLE_GROUPS_X_RESOURCES = 'core_groups_x_resources';



    #[Column('id_group', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;
}
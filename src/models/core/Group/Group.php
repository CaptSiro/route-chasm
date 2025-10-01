<?php

namespace models\core\Group;

use components\core\SaveError\SaveError;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\DatabaseAction;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;

/**
 * @property int $id
 * @property string $name
 */

#[Grid(proxy: new GroupProxy())]
#[Table('core_group')]
#[Database(App::DATABASE)]
class Group extends Model implements Editable {
    public const DEFAULT = 'Default';
    public const ADMIN = 'Admin';
    public const ROOT = 'Root';
    public const TABLE_GROUPS_X_RESOURCES = 'core_groups_x_resources';

    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }



    use EditableExtension;

    #[Column('id_group', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;



    public function save(): DatabaseAction|View {
        if (!is_null(static::fromName($this->name))) {
            return new SaveError('name', 'Name is already taken');
        }

        if ($this->isNewRecord()) {
            $this->setEditable(true);
        }

        return parent::save();
    }
}
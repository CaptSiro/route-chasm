<?php

namespace models\core;

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

/**
 * @property int $id
 */

#[Grid]
#[Table('core_resource')]
#[Database(App::DATABASE)]
class Resource extends Model {
    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }



    #[Column('id_resource', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;



    public function save(): DatabaseAction|View {
        if ($this->isNewRecord()) {
            $resource = static::fromName($this->name);
            if (!is_null($resource)) {
                return new SaveError('name', 'Name is already taken');
            }
        }

        return parent::save();
    }
}
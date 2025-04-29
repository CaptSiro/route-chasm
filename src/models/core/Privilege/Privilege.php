<?php

namespace models\core\Privilege;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Action;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;
use modules\forms\description\TextField;

#[Grid(new PrivilegeProxy())]
#[Table('core_privilege')]
#[Database(App::DATABASE)]
class Privilege extends Model implements Editable {
    use EditableExtension;

    #[Column(type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;



    public function save(): Action|View {
        $this->setEditable(true);
        return parent::save();
    }
}
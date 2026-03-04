<?php

namespace models\core\Privilege;

use components\core\SaveError\SaveError;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\DatabaseAction;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;

#[Grid(proxy: new PrivilegeProxy())]
#[Table('core_privilege')]
#[Database(App::DATABASE)]
class Privilege extends Model implements Editable {
    use ModelCache;



    public const READ = 'Read';
    public const CREATE = 'Create';
    public const UPDATE = 'Update';

    public static function fromName(string $name): ?static {
        static::modelCache_loadAll(fn(Privilege $x) => $x->name);

        if (!is_null($hit = static::modelCache_get($name))) {
            return $hit;
        }

        return static::modelCache_set($name, static::first(
            where: Query::infer('name = ?', $name)
        ));
    }



    use EditableExtension;

    #[Column('id_privilege', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;



    public function getHumanIdentifier(): string {
        return $this->name;
    }

    public function save(): DatabaseAction|View {
        if ($this->isNewRecord()) {
            $privilege = self::fromName($this->name);
            if (!is_null($privilege)) {
                return new SaveError('name', 'Name is already taken');
            }

            $this->setEditable(true);
        }

        return parent::save();
    }
}
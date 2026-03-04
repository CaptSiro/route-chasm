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
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;

#[Grid(proxy: new GroupProxy())]
#[Table('core_group')]
#[Database(App::DATABASE)]
class Group extends Model implements Editable {
    public const NAME_DEFAULT = 'Default';
    public const NAME_ADMIN = 'Admin';
    public const NAME_ROOT = 'Root';
    public const TABLE_GROUPS_X_RESOURCES = 'core_groups_x_resources';

    public const MAPPING_RESOURCE = 'id_resource';
    public const MAPPING_PRIVILEGE = 'id_privilege';

    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }



    use EditableExtension;

    #[Column('id_group', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;



    // Model
    public function getHumanIdentifier(): string {
        return $this->name;
    }

    public function save(): DatabaseAction|View {
        if (!is_null(static::fromName($this->name))) {
            return new SaveError('name', 'Name is already taken');
        }

        if ($this->isNewRecord()) {
            $this->setEditable(true);
        }

        return parent::save();
    }

    public function delete(): DatabaseAction {
        $this->clearMappings();
        return parent::delete();
    }



    /**
     * @return array<array<string, int>> array of ['id_resource' => int, 'id_privilege' => int]
     */
    public function getMappings(): array {
        $connection = static::getDescription()->getConnection();
        $driver = $connection->getDriver();

        $gr = $driver->escapeTable(self::TABLE_GROUPS_X_RESOURCES);

        $sql = Sql::select($gr)
            ->projection("$gr.id_resource")
            ->projection("$gr.id_privilege")
            ->where(Query::infer("$gr.id_group = ?", $this->id));

        return $sql->fetchAll($connection);
    }

    public function clearMappings(): SideEffect {
        $connection = static::getDescription()->getConnection();

        $sql = Sql::delete(self::TABLE_GROUPS_X_RESOURCES)
            ->where(Query::infer("id_group = ?", $this->id));

        return $sql->run($connection);
    }

    /**
     * @param array<array<string, int>> $mappings array of ['id_resource' => int, 'id_privilege' => int]
     * @return SideEffect
     */
    public function addMappingsRaw(array $mappings): SideEffect {
        if (count($mappings) === 0) {
            return SideEffect::none();
        }

        $connection = static::getDescription()->getConnection();

        $sql = Sql::insert(self::TABLE_GROUPS_X_RESOURCES)
            ->columns(['id_group', 'id_resource', 'id_privilege']);

        foreach ($mappings as $mapping) {
            $sql->value([
                Parameter::infer($this->id),
                Parameter::infer($mapping[self::MAPPING_RESOURCE]),
                Parameter::infer($mapping[self::MAPPING_PRIVILEGE]),
            ]);
        }

        return $sql->run($connection);
    }
}
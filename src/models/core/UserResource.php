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
use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\select\Select;
use core\forms\description\TextField;
use core\view\View;

#[Grid]
#[Table('core_resource')]
#[Database(App::DATABASE)]
class UserResource extends Model {
    use ModelCache;

    public const TYPE_SYSTEM = 1;
    public const TYPE_USER = 2;
    public const TYPES = [
        self::TYPE_SYSTEM => 'System',
        self::TYPE_USER => 'User'
    ];



    public static function allOfType(int $type): array {
        return self::all(where: Query::infer('type = ?', $type));
    }

    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }

    public static function getSystemResource(string $name): ?static {
        if (!is_null($hit = static::modelCache_get($name))) {
            return $hit;
        }

        $resource = static::first(
            where: Query::infer('name = ? AND type = ?', $name, self::TYPE_SYSTEM)
        );

        if (is_null($resource)) {
            $resource = static::create([
                'name' => $name,
                'type' => self::TYPE_SYSTEM
            ]);
        }

        return static::modelCache_set($name, $resource);
    }



    #[Column('id_resource', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;

    #[Select(UserResource::TYPES, label: 'Types')]
    #[Column(type: Column::TYPE_INTEGER)]
    public int $type;



    public function getHumanIdentifier(): string {
        return $this->name;
    }



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
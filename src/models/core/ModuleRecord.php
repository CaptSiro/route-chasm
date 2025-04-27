<?php

namespace models\core;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use core\module\ModuleInfo;

/**
 * @property string $identifier
 * @property string $version
 */

#[Grid]
#[Table('core_modules')]
#[Database(App::DATABASE)]
class ModuleRecord extends Model {
    public static function createFromInfo(ModuleInfo $info): static {
        $module = new static();

        $module->identifier = $info->identifier;
        $module->version = $info->version;

        $module->save();
        return $module;
    }



    #[GridColumn]
    #[Column(type: Column::TYPE_STRING, primaryKey: true)]
    protected string $identifier;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $version;
}
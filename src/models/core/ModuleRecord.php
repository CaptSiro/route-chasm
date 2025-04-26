<?php

namespace models\core;

use core\App;
use core\database_v3\sql\Column;
use core\database_v3\sql\Database;
use core\database_v3\sql\Model;
use core\database_v3\sql\Table;
use core\module\ModuleInfo;

/**
 * @property string $identifier
 * @property string $version
 */

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



    #[Column(type: Column::TYPE_STRING, primaryKey: true)]
    protected string $identifier;

    #[Column(type: Column::TYPE_STRING)]
    protected string $version;
}
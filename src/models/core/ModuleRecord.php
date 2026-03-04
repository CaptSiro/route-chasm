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

#[Grid]
#[Table('core_module')]
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
    #[Column(type: Column::TYPE_STRING, isPrimaryKey: true)]
    public string $identifier;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $version;



    public function getHumanIdentifier(): string {
        return $this->identifier;
    }
}
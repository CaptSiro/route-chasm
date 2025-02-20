<?php

namespace entities\core;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\Schema;
use core\database\pdo\PdoTable;
use core\module\ModuleInfo;



Entity::addSchema(ModuleDefinition::class,  new Schema(
    new PdoTable(
        'core_modules',
        [
            'identifier' => new PrimaryKey(),
            'version' => Text::getInstance()
        ],
        'identifier'
    )
));



/**
 * @property string identifier
 * @property string version
 */
class ModuleDefinition extends Entity {
    public static function createFromInfo(ModuleInfo $info): static {
        $module = new static();

        $module->identifier = $info->identifier;
        $module->version = $info->version;

        $module->save();
        return $module;
    }
}
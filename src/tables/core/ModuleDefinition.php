<?php

namespace tables\core;

use core\database\DatabaseColumns;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\Table;
use core\module\ModuleInfo;

/**
 * @property string identifier
 * @property string version
 */
class ModuleDefinition extends Table {
    use DatabaseColumns;

    public static function init(): void {
        self::$columns = [
            'identifier' => new PrimaryKey(),
            'version' => Text::getInstance()
        ];

        parent::init();
    }

    public static function getTable(): string {
        return 'core_modules';
    }


    
    public static function createFromInfo(ModuleInfo $info): static {
        $module = new static();

        $module->identifier = $info->identifier;
        $module->version = $info->version;

        $module->save();
        return $module;
    }
}
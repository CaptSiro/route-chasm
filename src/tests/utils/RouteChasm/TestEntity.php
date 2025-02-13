<?php

namespace tests\utils\RouteChasm;

use core\database\Entity;
use core\database\pdo\PdoTable;
use core\database\StaticTableDefinition;

class TestEntity extends Entity {
    use StaticTableDefinition;

    public static function init(): void {
        self::$definition = new PdoTable('test', []);
    }
}
<?php

namespace tests\utils\RouteChasm;

use core\database\Entity;

class TestEntity extends Entity {
    public static function getTable(): string {
        return "table";
    }

    public static function getColumns(): array {
        return [];
    }
}
<?php

namespace tests\utils\RouteChasm;

use core\database\Table;

class TestTable extends Table {
    public static function getTable(): string {
        return "table";
    }

    public static function getColumns(): array {
        return [];
    }
}
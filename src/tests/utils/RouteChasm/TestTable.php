<?php

namespace tests\utils\RouteChasm;

use core\Table;

class TestTable extends Table {
    public static function init(): void {
        self::$table = "tabTest";
        self::$columns = [];
    }
}
<?php

namespace tests\utils\RouteChasm;

use core\database\Table;
use core\Request;
use core\Resource;
use core\Singleton;

class TestResource extends Resource {
    use Singleton;

    protected function getTable(): string {
        return "";
    }
}
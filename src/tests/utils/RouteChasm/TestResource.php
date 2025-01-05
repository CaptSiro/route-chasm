<?php

namespace tests\utils\RouteChasm;

use core\Resource;
use core\Singleton;

class TestResource extends Resource {
    use Singleton;

    protected function getTable(): string {
        return "";
    }
}
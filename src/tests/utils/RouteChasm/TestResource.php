<?php

namespace tests\utils\RouteChasm;

use core\database\sql\ModelDescription;
use core\Resource;
use core\Singleton;
use models\core\Setting\Setting;

class TestResource extends Resource {
    use Singleton;

    public function __construct() {
        parent::__construct(ModelDescription::extract(Setting::class));
    }
}
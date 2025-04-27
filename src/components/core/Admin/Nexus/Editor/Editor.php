<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\AdminNexus;
use core\database\sql\Model;
use core\endpoints\Endpoint;

interface Editor extends Endpoint {
    public function setContext(AdminNexus $context): static;

    public function setModel(Model $model): static;
}
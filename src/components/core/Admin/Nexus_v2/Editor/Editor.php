<?php

namespace components\core\Admin\Nexus_v2\Editor;

use components\core\Admin\Nexus_v2\AdminNexus;
use core\database_v3\sql\Model;
use core\endpoints\Endpoint;

interface Editor extends Endpoint {
    public function setContext(AdminNexus $context): static;

    public function setModel(Model $model): static;
}
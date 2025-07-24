<?php

namespace components\core\Admin\Nexus;

use core\actions\Action;
use core\database\sql\Model;

interface Editor extends Action {
    public function setContext(AdminNexus $context): static;

    public function setModel(Model $model): static;
}
<?php

namespace components\core\Admin\Nexus;

use core\actions\Action;
use core\database\sql\Model;
use core\view\View;

interface Editor extends Action, View {
    public function setContext(AdminNexus $context): static;

    public function setModel(Model $model): static;
}
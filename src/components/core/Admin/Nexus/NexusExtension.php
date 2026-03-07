<?php

namespace components\core\Admin\Nexus;

use core\route\Router;

interface NexusExtension {
    public function onBind(AdminNexus $context, Router $router): void;
}
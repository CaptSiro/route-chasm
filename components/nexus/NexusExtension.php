<?php

namespace components\nexus;

use core\route\Router;

interface NexusExtension {
    public function onBind(Nexus $context, Router $router): void;
}
<?php

namespace core\module;

use Closure;
use core\route\Router;

interface Loader {
    public function on(string $event, Closure $function): void;

    public function dispatch(string $event, mixed $context): void;

    public function getMainRouter(): Router;
}
<?php

namespace core;

use Closure;

interface Loader {
    public function on(string $event, Closure $function): void;

    public function dispatch(string $event, mixed $context): void;

    public function getMainRouter(): Router;
}
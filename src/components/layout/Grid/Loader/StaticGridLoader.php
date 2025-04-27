<?php

namespace components\layout\Grid\Loader;

use Closure;
use components\layout\Grid\Grid;

class StaticGridLoader implements GridLoader {
    /**
     * @param Closure $loader Callback signature: fn() => array
     */
    public function __construct(
        protected Closure $loader
    ) {}

    public function load(Grid $context): array {
        return ($this->loader)();
    }
}
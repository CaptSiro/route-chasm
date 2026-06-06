<?php

namespace components\layout\Grid\Loader;

use Closure;
use components\layout\Grid\GridLayout;

class StaticGridLoader implements GridLoader {
    /**
     * @param Closure $loader Callback signature: fn() => array
     */
    public function __construct(
        protected Closure $loader
    ) {}

    public function load(GridLayout $context): array {
        return ($this->loader)();
    }
}
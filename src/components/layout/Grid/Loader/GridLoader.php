<?php

namespace components\layout\Grid\Loader;

use components\layout\Grid\Grid;

interface GridLoader {
    public function load(Grid $context): array;
}
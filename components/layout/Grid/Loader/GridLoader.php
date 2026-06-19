<?php

namespace components\layout\Grid\Loader;

use components\layout\Grid\GridLayout;

interface GridLoader {
    public function load(GridLayout $context): array;
}
<?php

namespace components\layout\Grid\Loader;

use components\layout\Grid\GridLayout;
use components\layout\Pagination\PortionUrlCreator;
use core\RouteChasmEnvironment;
use core\url\Url;

class GridLoaderUrlCreator extends PortionUrlCreator {
    public function __construct(
        Url $base,
        protected GridLayout $context
    ) {
        parent::__construct($base, RouteChasmEnvironment::QUERY_GRID_PORTION);
    }
}
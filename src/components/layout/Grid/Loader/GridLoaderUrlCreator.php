<?php

namespace components\layout\Grid\Loader;

use components\core\PaginationControl\PaginationUrlCreator;
use components\layout\Grid\GridLayout;
use core\communication\Request;
use core\RouteChasmEnvironment;
use core\url\Url;

class GridLoaderUrlCreator implements PaginationUrlCreator {
    public static function getPortion(Request $request): int {
        return $request->getUrl()->getQuery()->get(
            RouteChasmEnvironment::QUERY_GRID_PORTION,
            1
        );
    }

    public function __construct(
        protected Url $base,
        protected GridLayout $context
    ) {}

    public function createUrl(int $position, int $current, int $max): Url {
        $url = $this->base->copy();
        $url->setQueryArgument(RouteChasmEnvironment::QUERY_GRID_PORTION, $position);
        return $url;
    }
}
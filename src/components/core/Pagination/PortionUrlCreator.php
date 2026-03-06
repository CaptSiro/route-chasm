<?php

namespace components\core\Pagination;

use core\communication\Request;
use core\RouteChasmEnvironment;
use core\url\Url;

class PortionUrlCreator implements PaginationUrlCreator {
    public static function getPortion(Request $request): int {
        return $request->getUrl()->getQuery()->get(
            RouteChasmEnvironment::QUERY_PORTION,
            1
        );
    }



    public function __construct(
        protected Url $base,
        protected string $portionQuery = RouteChasmEnvironment::QUERY_PORTION
    ) {}



    public function createUrl(int $position, int $current, int $max): Url {
        return $this->base
            ->copy()
            ->setQueryArgument($this->portionQuery, $position);
    }
}
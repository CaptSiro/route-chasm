<?php

namespace components\core\Pagination;

use core\url\Url;

interface PaginationUrlCreator {
    public function createUrl(int $position, int $current, int $max): Url;
}
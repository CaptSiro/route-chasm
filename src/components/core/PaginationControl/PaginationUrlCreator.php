<?php

namespace components\core\PaginationControl;

use core\url\Url;

interface PaginationUrlCreator {
    public function createUrl(int $position, int $current, int $max): Url;
}
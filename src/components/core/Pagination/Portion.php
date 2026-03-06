<?php

namespace components\core\Pagination;

use core\database\sql\query\SelectQuery;

trait Portion {
    protected function calculateMax(int $portionSize, int $count): int {
        return $max = intval(ceil($count / $portionSize));
    }

    protected function calculateCurrent(int $portionSize, int $count, int $portion): int {
        return min(max(1, $portion), $this->calculateMax($portionSize, $count));
    }

    protected function setQueryLimit(SelectQuery $query, int $current, int $portionSize): SelectQuery {
        return $query
            ->limit($portionSize)
            ->offset(($current - 1) * $portionSize);
    }
}
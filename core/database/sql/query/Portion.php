<?php

namespace core\database\sql\query;

interface Portion {
    public const PARAMETER_COUNT = 'count';
    public const PARAMETER_OFFSET = 'offset';

    public function count(int $n): mixed;

    public function offset(int $offset): mixed;
}
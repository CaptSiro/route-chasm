<?php

namespace core\database_v3\sql\query;

interface Portion {
    public const PARAMETER_COUNT = 'count';
    public const PARAMETER_OFFSET = 'offset';

    public function count(int $n): mixed;

    public function offset(int $offset): mixed;
}
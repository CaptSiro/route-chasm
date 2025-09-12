<?php

namespace core\database\sql\query\clause;

use core\database\sql\query\Query;

readonly class JoinClause {
    public const TYPE_INNER = 'inner';
    public const TYPE_OUTER = 'outer';
    public const TYPE_LEFT = 'left';
    public const TYPE_RIGHT = 'right';
    public const TYPE_NATURAL = 'natural';



    public function __construct(
        public string $type,
        public string $table,
        public string|Query $condition
    ) {}



    public function getSql(): string {
        if ($this->type === self::TYPE_NATURAL) {
            return 'NATURAL JOIN '. $this->table;
        }

        return strtoupper($this->type) .' JOIN '. $this->table .' ON '. $this->condition;
    }

    public function getParameters(): array {
        return Query::unwrapParameters($this->condition);
    }
}
<?php

namespace core\database\sql;

readonly class SideEffect {
    public function __construct(
        public mixed $lastInsertedId,
        public int $rowsAffected,
    ) {}
}
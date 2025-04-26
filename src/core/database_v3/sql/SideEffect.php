<?php

namespace core\database_v3\sql;

readonly class SideEffect {
    public function __construct(
        public mixed $lastInsertedId,
        public int $rowsAffected,
    ) {}
}
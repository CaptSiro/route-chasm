<?php

namespace core\database\sql;

class SideEffect {
    public function __construct(
        protected mixed $lastInsertedId,
        protected int $rowsAffected,
    ) {}



    public function getLastInsertedId(): mixed {
        return $this->lastInsertedId;
    }

    public function getRowsAffected(): int {
        return $this->rowsAffected;
    }
}
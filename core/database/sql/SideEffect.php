<?php

namespace core\database\sql;

class SideEffect {
    public static function none(): SideEffect {
        return new self(0, 0);
    }



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
<?php

namespace core\database;

readonly class SideEffect {
    public function __construct(
        protected int $lastInsertedId,
        protected int $rowCount,
    ) {}



    /**
     * @return int
     */
    public function getLastInsertedId(): int {
        return $this->lastInsertedId;
    }

    /**
     * @return int
     */
    public function getRowCount(): int {
        return $this->rowCount;
    }
}
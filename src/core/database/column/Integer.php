<?php

namespace core\database\column;

use core\Singleton;

class Integer implements Column {
    use Singleton;



    public function __construct(
        protected bool $isAutoIncrement
    ) {}



    public function transform(mixed $value): mixed {
        return intval($value);
    }

    public function isVirtual(): bool {
        return false;
    }

    public function isAutoCreated(): bool {
        return $this->isAutoIncrement;
    }
}
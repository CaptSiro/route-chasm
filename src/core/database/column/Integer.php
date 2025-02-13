<?php

namespace core\database\column;

use core\database\Column;
use core\Singleton;

class Integer implements Column {
    use Singleton;



    public function __construct(
        protected bool $isAutoIncrement
    ) {}



    public function transform(mixed $value): int {
        return intval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return $this->isAutoIncrement;
    }
}
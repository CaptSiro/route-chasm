<?php

namespace core\database\column;

use core\Singleton;

class Decimal implements Column {
    use Singleton;



    public function transform(mixed $value): mixed {
        return floatval($value);
    }

    public function isVirtual(): bool {
        return false;
    }
}
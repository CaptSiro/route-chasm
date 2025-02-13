<?php

namespace core\database\column;

use core\database\Column;
use core\Singleton;

class Decimal implements Column {
    use Singleton;



    public function transform(mixed $value): float {
        return floatval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
<?php

namespace core\database\column;

use core\database\Column;
use core\Singleton;

class Boolean implements Column {
    use Singleton;



    public function transform(mixed $value): bool {
        return boolval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
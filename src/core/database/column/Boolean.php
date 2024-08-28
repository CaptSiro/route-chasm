<?php

namespace core\database\column;

use core\Singleton;

class Boolean implements Column {
    use Singleton;



    public function transform(mixed $value): mixed {
        return boolval($value);
    }

    public function isVirtual(): bool {
        return false;
    }
}
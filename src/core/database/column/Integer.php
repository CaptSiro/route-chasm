<?php

namespace core\database\column;

use core\Singleton;

class Integer implements Column {
    use Singleton;



    public function transform(mixed $value): mixed {
        return intval($value);
    }

    public function isVirtual(): bool {
        return false;
    }
}
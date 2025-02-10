<?php

namespace core\database\pdo\column;

use core\Singleton;

class Boolean implements Column {
    use Singleton;



    public function transform(mixed $value): bool {
        return boolval($value);
    }

    public function isVirtual(): bool {
        return false;
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
<?php

namespace core\database\pdo\column;

use core\Singleton;

class Text implements Column {
    use Singleton;



    public function transform(mixed $value): ?string {
        if (is_null($value)) {
            return null;
        }

        return (string) $value;
    }

    public function isVirtual(): bool {
        return false;
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
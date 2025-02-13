<?php

namespace core\database\column;

use core\database\Column;
use core\Singleton;

class Text implements Column {
    use Singleton;



    public function transform(mixed $value): ?string {
        if (is_null($value)) {
            return null;
        }

        return (string) $value;
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
<?php

namespace core\database\column;

use core\Singleton;

class Text implements Column {
    use Singleton;



    public function transform(mixed $value): mixed {
        return (string) $value;
    }

    public function isVirtual(): bool {
        return false;
    }
}
<?php

namespace core\database\column;

interface Column {
    public function transform(mixed $value): mixed;

    public function isVirtual(): bool;
}
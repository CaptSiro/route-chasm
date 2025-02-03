<?php

namespace core\database\pdo\column;

interface Column {
    public function transform(mixed $value): mixed;

    public function isVirtual(): bool;

    public function isAutoCreated(): bool;
}
<?php

namespace core\database;

interface Column {
    public function transform(mixed $value): mixed;

    public function isVirtual(mixed $value): bool;

    public function isAutoCreated(): bool;
}
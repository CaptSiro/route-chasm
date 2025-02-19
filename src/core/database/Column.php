<?php

namespace core\database;

use modules\forms\definition\overrides\FieldDefinition;

interface Column {
    public function transform(mixed $value): mixed;

    public function isVirtual(mixed $value): bool;

    public function isAutoCreated(): bool;

    public function getFieldDefinition(string $name): FieldDefinition;
}
<?php

namespace core\database\column;

use core\database\Column;
use core\database\ColumnNameOverride;
use modules\forms\controls\NumberField;
use modules\forms\definition\overrides\FieldDefinition;
use modules\forms\definition\overrides\FieldOverride;

class Integer implements Column {
    use ColumnNameOverride;



    public function __construct(
        protected bool $isAutoIncrement = false
    ) {}



    public function transform(mixed $value): int {
        return intval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return $this->isAutoIncrement;
    }

    public function getFieldDefinition(string $name): FieldDefinition {
        return new FieldOverride($this->name ?? $name, new NumberField($name, $name));
    }
}
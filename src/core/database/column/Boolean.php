<?php

namespace core\database\column;

use core\database\Column;
use core\database\ColumnLabelOverride;
use modules\forms\controls\Checkbox\Checkbox;
use modules\forms\definition\overrides\FieldOverride;
use modules\forms\definition\overrides\FieldDefinition;

class Boolean implements Column {
    use ColumnLabelOverride;



    public function transform(mixed $value): bool {
        return boolval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return false;
    }

    public function getFieldDefinition(string $name): FieldDefinition {
        return new FieldOverride($this->label ?? $name, new Checkbox($name, $name));
    }
}
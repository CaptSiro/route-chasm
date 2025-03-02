<?php

namespace core\database\column;

use core\database\Column;
use core\database\ColumnNameOverride;
use modules\forms\controls\TextField;
use modules\forms\definition\overrides\FieldDefinition;
use modules\forms\definition\overrides\FieldOverride;

class Text implements Column {
    use ColumnNameOverride;



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

    public function getFieldDefinition(string $name): FieldDefinition {
        return new FieldOverride($this->name ?? $name, new TextField($name, $name));
    }
}
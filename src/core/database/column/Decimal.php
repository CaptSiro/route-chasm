<?php

namespace core\database\column;

use core\database\Column;
use core\Singleton;
use modules\forms\controls\NumberField;
use modules\forms\definition\overrides\FieldDefinition;
use modules\forms\definition\overrides\FieldOverride;

class Decimal implements Column {
    use Singleton;



    public function transform(mixed $value): float {
        return floatval($value);
    }

    public function isVirtual(mixed $value): bool {
        return is_null($value);
    }

    public function isAutoCreated(): bool {
        return false;
    }

    public function getFieldDefinition(string $name): FieldDefinition {
        $field = new NumberField($name, $name);
        $field->step(0.01);

        return new FieldOverride($name, $field);
    }
}
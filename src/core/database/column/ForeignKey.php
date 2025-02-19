<?php

namespace core\database\column;

use core\database\Column;
use http\Exception\InvalidArgumentException;
use modules\forms\controls\NumberField;
use modules\forms\definition\overrides\FieldDefinition;
use modules\forms\definition\overrides\FieldOverride;

class ForeignKey implements Column {
    public function __construct(
        protected string $class,
        protected string $alias
    ) {
        if (!method_exists($class, "fromId")) {
            throw new InvalidArgumentException("Provided class '$class' must implement 'fromId' method");
        }
    }



    public function transform(mixed $value): mixed {
        return call_user_func("$this->class::fromId", $value);
    }

    public function isVirtual(mixed $value): bool {
        return true;
    }

    public function getAlias(): string {
        return $this->alias;
    }

    public function isAutoCreated(): bool {
        return false;
    }

    public function getFieldDefinition(string $name): FieldDefinition {
        // todo change to select (?)
        return new FieldOverride($name, new NumberField($name, $name));
    }
}
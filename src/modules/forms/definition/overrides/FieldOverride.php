<?php

namespace modules\forms\definition\overrides;

use core\view\View;
use modules\forms\controls\Control;

class FieldOverride implements FieldDefinition {
    public function __construct(
        protected string $label,
        protected Control $component
    ) {
        $this->component->setLabel($this->label);
    }



    public function include(): bool {
        return true;
    }

    public function getComponent(mixed $value): ?View {
        $this->component->setValue($value);
        return $this->component;
    }

    public function setName(string $name): void {
        $this->component->setName($name);
    }
}
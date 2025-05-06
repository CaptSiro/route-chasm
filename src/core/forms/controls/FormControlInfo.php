<?php

namespace core\forms\controls;

trait FormControlInfo {
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setValue(mixed $value): void {
        $this->value = $value;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }
}
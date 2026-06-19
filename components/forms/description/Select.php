<?php

namespace components\forms\description;

use Attribute;
use components\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Select implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected SelectValues|array $values,
        protected ?string $label = null,
        protected ?string $selected = null,
        protected bool $isFirst = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getValues(): array {
        if (!is_array($this->values)) {
            return $this->values->getValues();
        }

        return $this->values;
    }

    public function getControl(): Control {
        return new \components\forms\controls\Select(
            $this->name,
            $this->label,
            $this->getValues(),
            $this->selected
        );
    }
}
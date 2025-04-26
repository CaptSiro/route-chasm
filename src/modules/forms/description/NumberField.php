<?php

namespace modules\forms\description;

use Attribute;
use modules\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NumberField implements ControlAttribute {
    use BindProperty;

    public function __construct(
        protected ?string $label = null,
        protected bool $readonly = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \modules\forms\controls\NumberField($this->name, $this->label);

        if ($this->readonly) {
            $control->readonly();
        }

        return $control;
    }
}
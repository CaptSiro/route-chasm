<?php

namespace core\forms\description;

use Attribute;
use core\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TextField implements ControlAttribute {
    use BindProperty;

    public function __construct(
        protected ?string $label = null,
        protected bool $readonly = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \core\forms\controls\TextField($this->name, $this->label);

        if ($this->readonly) {
            $control->readonly();
        }

        return $control;
    }
}
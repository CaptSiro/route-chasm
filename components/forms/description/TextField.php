<?php

namespace components\forms\description;

use Attribute;
use components\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TextField implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected ?string $label = null,
        protected bool $readonly = false,
        protected bool $isFirst = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \components\forms\controls\TextField($this->name, $this->label);

        if ($this->readonly) {
            $control->readonly();
        }

        return $control;
    }
}
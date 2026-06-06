<?php

namespace components\forms\description;

use Attribute;
use components\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected ?string $label = null,
        protected bool $isFirst = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        return new \components\forms\controls\Date($this->name, $this->label);
    }
}
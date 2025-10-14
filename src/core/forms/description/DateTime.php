<?php

namespace core\forms\description;

use Attribute;
use core\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTime implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected ?string $label = null,
        protected bool $isFirst = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        return new \core\forms\controls\DateTime($this->name, $this->label);
    }
}
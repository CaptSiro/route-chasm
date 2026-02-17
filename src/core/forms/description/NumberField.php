<?php

namespace core\forms\description;

use Attribute;
use core\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NumberField implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected ?string $label = null,
        protected bool $readonly = false,
        protected bool $isFirst = false,
        protected float $step = 1,
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \core\forms\controls\NumberField($this->name, $this->label);
        $control->addAttribute('step', $this->step);

        if ($this->readonly) {
            $control->readonly();
        }

        return $control;
    }
}
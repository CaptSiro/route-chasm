<?php

namespace modules\forms\description;

use Attribute;
use modules\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Input implements ControlAttribute {
    use BindProperty;

    public function __construct(
        public string $type = "text",
        public ?string $label = null,
        public bool $readonly = false
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \modules\forms\controls\Input\Input(
            $this->type,
            $this->name,
            $this->label
        );

        if ($this->readonly) {
            $control->readonly();
        }

        return $control;
    }
}
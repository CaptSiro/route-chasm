<?php

namespace core\forms\description;

use Attribute;
use core\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Input implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected string $type = "text",
        protected ?string $label = null,
        protected bool $readonly = false,
        protected bool $isFirst = false
    ) {}



    public function getType(): string {
        return $this->type;
    }

    public function isReadonly(): bool {
        return $this->readonly;
    }

    public function getLabel(): string {
        return $this->label ?? "";
    }

    public function getControl(): Control {
        $control = new \core\forms\controls\Input\Input(
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
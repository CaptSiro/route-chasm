<?php

namespace core\forms\description;

use Attribute;
use core\forms\controls\Control;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TextArea implements ControlAttribute {
    use BindProperty, IsFirst;

    public function __construct(
        protected ?string $label = null,
        protected bool $readonly = false,
        protected bool $isFirst = false,
        protected ?int $rows = null,
        protected ?int $columns = null,
    ) {}



    public function getLabel(): string {
        return $this->label;
    }

    public function getControl(): Control {
        $control = new \core\forms\controls\TextArea\TextArea($this->name, $this->label);

        if ($this->readonly) {
            $control->addAttribute('readonly');
        }

        if (!is_null($this->rows)) {
            $control->addAttribute('rows', $this->rows);
        }

        if (!is_null($this->columns)) {
            $control->addAttribute('columns', $this->columns);
        }

        return $control;
    }
}
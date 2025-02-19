<?php

namespace modules\forms\controls\Submit;

use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Submit implements Control {
    use Renderer, FormControl;

    public function __construct(
        protected string $label = "Submit"
    ) {}

    public function setValue(mixed $value): void {
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setName(string $name): void {
    }
}
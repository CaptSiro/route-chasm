<?php

namespace modules\forms\controls\TextArea;

use core\Attributes;
use core\CssClass;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class TextArea implements Control {
    use Renderer, FormControl, CssClass, Attributes;

    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected ?string $value = null,
    ) {}

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setValue(mixed $value): void {
        $this->value = $value;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }
}
<?php

namespace core\forms\controls\TextArea;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

class TextArea implements Control, Attribute {
    use Renderer, FormControl, HtmlAttribute;

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
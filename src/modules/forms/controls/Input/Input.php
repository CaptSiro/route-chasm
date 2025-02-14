<?php

namespace modules\forms\controls\Input;

use core\Attributes;
use core\CssClass;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Input implements Control {
    use Renderer, FormControl, CssClass, Attributes;



    public function __construct(
        protected string $type,
        protected string $name,
        protected string $label,
        protected ?string $value = null,
    ) {
        $this->attributes = [];
        $this->setTemplate(self::getStaticSource("Input.phtml"));
    }



    public function getFieldName(): ?string {
        return $this->name;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function pattern(string $pattern): self {
        $this->addAttribute("pattern", $pattern);
        return $this;
    }

    public function required(): self {
        $this->addAttribute("required", true);
        return $this;
    }
}
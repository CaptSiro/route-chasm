<?php

namespace modules\forms\controls\Input;

use core\html\HtmlAttribute;
use core\html\Attribute;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Input implements Control, Attribute {
    use Renderer, FormControl, HtmlAttribute;



    public function __construct(
        protected string $type,
        protected string $name,
        protected string $label = self::class,
        protected string $value = '',
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

    public function pattern(string $pattern): static {
        $this->addAttribute("pattern", $pattern);
        return $this;
    }

    public function required(): static {
        $this->addAttribute("required", true);
        return $this;
    }

    public function readonly(): static {
        $this->addAttribute('readonly');
        return $this;
    }

    public function setValue(mixed $value): void {
        $this->value = $value;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }
}
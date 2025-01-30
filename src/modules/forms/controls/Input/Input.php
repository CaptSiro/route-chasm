<?php

namespace modules\forms\controls\Input;

use core\Attributes;
use core\CssClass;
use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Input implements Control {
    use TemplateRenderer, FormControl, CssClass, Attributes;



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

    public function pattern(string $pattern): self {
        $this->addAttribute("pattern", $pattern);
        return $this;
    }

    public function required(): self {
        $this->addAttribute("required", true);
        return $this;
    }
}
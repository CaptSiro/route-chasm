<?php

namespace core\forms\controls\Select;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

class Select implements Control, Attribute {
    use Renderer, FormControl, HtmlAttribute;




    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected array $values = [],
        protected ?string $selected = null
    ) {
        $this->setPlaceholder('Type to search');
    }



    public function getFieldName(): ?string {
        return $this->name;
    }

    public function setPlaceholder(string $placeholder): static {
        $this->addAttribute('placeholder', $placeholder);
        return $this;
    }



    // Control
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setValue(mixed $value): void {
        $this->selected = $value;
    }

    public function setValues(array $values): void {
        $this->values = $values;
    }
}
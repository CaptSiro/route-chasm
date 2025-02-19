<?php

namespace modules\forms\controls\Select;

use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Select implements Control {
    use Renderer, FormControl;



    protected string $cssClass;
    protected array $attributes;

    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected array $values = [],
        protected ?string $selected = null
    ) {
        $this->cssClass = "";
        $this->attributes = [];
    }



    public function getFieldName(): ?string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setValue(mixed $value): void {
        $this->values = $value;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }
}
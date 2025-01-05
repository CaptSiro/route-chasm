<?php

namespace modules\forms\controls\Select;

use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Select implements Control {
    use TemplateRenderer, FormControl;



    protected string $cssClass;
    protected array $attributes;

    public function __construct(
        protected string $name,
        protected string $label,
        protected array $values,
        protected ?string $selected = null
    ) {
        $this->cssClass = "";
        $this->attributes = [];
    }



    public function getFieldName(): ?string {
        return $this->name;
    }
}
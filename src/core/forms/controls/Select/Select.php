<?php

namespace core\forms\controls\Select;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\view\Renderer;

class Select implements Control {
    use Renderer, FormControl, FormControlInfo;



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
}
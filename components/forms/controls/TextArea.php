<?php

namespace components\forms\controls;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\view\Renderer;

class TextArea implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute;

    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected ?string $value = null,
    ) {
        $this->addAttribute('rows', '10');
    }
}
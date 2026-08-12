<?php

namespace components\forms\controls;

use core\view\Attribute;
use core\view\HtmlAttribute;
use core\view\ViewTemplateRenderer;

class TextArea implements Control, Attribute {
    use ViewTemplateRenderer, FormControl, FormControlInfo, HtmlAttribute;

    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected ?string $value = null,
    ) {
        $this->addAttribute('rows', '10');
    }
}
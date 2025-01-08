<?php

namespace modules\forms\controls\TextArea;

use core\Attributes;
use core\CssClass;
use core\view\Render;
use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class TextArea implements Render, Control {
    use TemplateRenderer, FormControl, CssClass, Attributes;

    public function __construct(
        protected string $name,
        protected string $label,
        protected ?string $value = null,
    ) {}
}
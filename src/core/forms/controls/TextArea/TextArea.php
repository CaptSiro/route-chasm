<?php

namespace core\forms\controls\TextArea;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

class TextArea implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute;

    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected ?string $value = null,
    ) {}
}
<?php

namespace components\forms\controls;

use components\html\HtmlAttribute;
use core\ResourceLoader;
use core\view\Renderer;
use core\view\ViewTemplate;

class Button implements ViewTemplate {
    use Renderer, ResourceLoader, HtmlAttribute;

    public function __construct(
        protected string $label,
        protected string $type = 'button',
    ) {}
}
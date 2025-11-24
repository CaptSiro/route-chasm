<?php

namespace core\forms\controls\Button;

use core\html\HtmlAttribute;
use core\ResourceLoader;
use core\view\Renderer;
use core\view\View;

class Button implements View {
    use Renderer, ResourceLoader, HtmlAttribute;

    public function __construct(
        protected string $label,
        protected string $type = 'button',
    ) {}
}
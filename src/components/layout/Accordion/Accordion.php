<?php

namespace components\layout\Accordion;

use core\view\Renderer;
use core\view\View;

class Accordion implements View {
    use Renderer;

    public function __construct(
        protected string $title,
        protected View $content,
        protected bool $isExpanded = true
    ) {}
}

<?php

namespace components\Accordion;

use core\view\Render;
use core\view\Renderer;

class Accordion implements Render {
    use Renderer;

    public function __construct(
        protected string $title,
        protected Render $content,
        protected bool $isExpanded = true
    ) {}
}
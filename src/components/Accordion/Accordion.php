<?php

namespace components\Accordion;

use core\view\Render;
use core\view\TemplateRenderer;

class Accordion implements Render {
    use TemplateRenderer;

    public function __construct(
        protected string $title,
        protected Render $content,
        protected bool $isExpanded = true
    ) {}
}
<?php

namespace components\layout;

use core\view\Renderer;
use core\view\View;
use core\view\ViewTemplate;

class Accordion implements ViewTemplate {
    use Renderer;

    public function __construct(
        protected string $title,
        protected View $content,
        protected bool $isExpanded = true
    ) {}
}

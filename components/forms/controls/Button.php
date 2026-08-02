<?php

namespace components\forms\controls;

use core\ResourceLoader;
use core\view\HtmlAttribute;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Button implements ViewTemplate {
    use ViewTemplateRenderer, ResourceLoader, HtmlAttribute;

    public function __construct(
        protected string $label,
        protected string $type = 'button',
    ) {}
}
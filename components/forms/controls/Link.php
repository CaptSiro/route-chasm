<?php

namespace components\forms\controls;

use core\ResourceLoader;
use core\view\HtmlAttribute;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Link implements ViewTemplate {
    use ViewTemplateRenderer, ResourceLoader, HtmlAttribute;

    public function __construct(
        string $url,
        protected string $label,
        string $target = '_self'
    ) {
        $this->addAttribute('href', $url);
        $this->addAttribute('target', $target);
    }
}
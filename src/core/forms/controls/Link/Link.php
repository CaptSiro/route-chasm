<?php

namespace core\forms\controls\Link;

use core\html\HtmlAttribute;
use core\ResourceLoader;
use core\view\Renderer;
use core\view\View;

class Link implements View {
    use Renderer, ResourceLoader, HtmlAttribute;

    public function __construct(
        string $url,
        protected string $label,
        string $target = '_self'
    ) {
        $this->addAttribute('href', $url);
        $this->addAttribute('target', $target);
    }
}
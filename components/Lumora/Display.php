<?php

namespace components\Lumora;

use components\Lumora\Editor\Editor;
use core\view\Controller;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class Display extends Controller {
    public function __construct(
        string $title,
        protected Editor $editor,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
        $this->setTitle($title);
    }
}
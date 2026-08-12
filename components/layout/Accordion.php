<?php

namespace components\layout;

use core\view\Component;
use core\view\Renderer;
use core\view\View;

class Accordion extends Component {
    public function __construct(
        string $title,
        protected View $content,
        protected bool $isExpanded = true,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        $this->setTitle($title);
    }



    public function setRenderer(Renderer $renderer): static {
        Component::propagateSetRenderer($this->content, $renderer);
        return parent::setRenderer($renderer);
    }
}

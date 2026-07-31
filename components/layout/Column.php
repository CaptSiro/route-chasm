<?php

namespace components\layout;

use components\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;
use core\view\Component;
use core\view\Container;
use core\view\renderers\HtmlRenderer;
use JsonSerializable;

class Column extends Component implements View, Container, JsonSerializable {
    use DynamicContainer, HtmlAttribute;



    /**
     * @param float $widthPercentage
     * @param array<View|string> $views
     * @param \core\view2\Renderer $renderer
     */
    public function __construct(
        float $widthPercentage = 1,
        array $views = [],
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);

        $this->widthPercentage = $widthPercentage;
        $this->views = $views;
    }
}
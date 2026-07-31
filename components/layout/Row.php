<?php

namespace components\layout;

use components\html\HtmlAttribute;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use core\view\View;
use core\view\Container;
use JsonSerializable;

class Row extends Component implements View, Container, JsonSerializable {
    use DynamicContainer, HtmlAttribute;



    /**
     * @param float $widthPercentage
     * @param array<View|string> $views
     * @param Renderer $renderer
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
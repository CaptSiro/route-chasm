<?php

namespace components\layout;

use core\view\Component;
use core\view\Container;
use core\view\HtmlAttribute;
use core\view\Renderer;
use core\view\View;
use JsonSerializable;

class Column extends Component implements View, Container, JsonSerializable {
    use DynamicContainer, HtmlAttribute;



    /**
     * @param float $widthPercentage
     * @param array<View|string> $views
     * @param Renderer|null $renderer
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
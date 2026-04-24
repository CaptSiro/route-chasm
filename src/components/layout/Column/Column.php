<?php

namespace components\layout\Column;

use components\layout\DynamicLayout;
use components\layout\Layout;
use core\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;

class Column implements View, Layout {
    use Renderer, DynamicLayout, HtmlAttribute;

    public function __construct(float $widthPercentage = 1, array $children = []) {
        $this->children = $children;
        $this->widthPercentage = $widthPercentage;
    }
}
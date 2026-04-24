<?php

namespace components\layout\Row;

use components\layout\DynamicLayout;
use components\layout\Layout;
use core\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;

class Row implements View, Layout {
    use Renderer, DynamicLayout, HtmlAttribute;

    public function __construct(float $widthPercentage = 1, array $children = []) {
        $this->children = $children;
        $this->widthPercentage = $widthPercentage;
    }
}
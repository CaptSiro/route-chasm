<?php

namespace components\layout;

use components\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;

class Row implements View, Layout {
    use Renderer, DynamicLayout, HtmlAttribute;

    public function __construct(float $widthPercentage = 1, array $children = []) {
        $this->children = $children;
        $this->widthPercentage = $widthPercentage;
    }
}
<?php

namespace components\layout\Column;

use components\layout\DynamicLayout;
use components\layout\Layout;
use core\view\Renderer;
use core\view\View;

class Column implements View, Layout {
    use Renderer, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
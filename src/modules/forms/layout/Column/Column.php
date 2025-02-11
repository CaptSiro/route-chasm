<?php

namespace modules\forms\layout\Column;

use core\view\Render;
use core\view\Renderer;
use modules\forms\layout\DynamicLayout;

class Column implements Render {
    use Renderer, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
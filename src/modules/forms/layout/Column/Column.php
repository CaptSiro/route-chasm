<?php

namespace modules\forms\layout\Column;

use core\view\View;
use core\view\Renderer;
use modules\forms\layout\DynamicLayout;

class Column implements View {
    use Renderer, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
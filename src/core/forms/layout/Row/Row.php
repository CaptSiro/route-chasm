<?php

namespace core\forms\layout\Row;

use core\forms\layout\DynamicLayout;
use core\view\Renderer;
use core\view\View;

class Row implements View {
    use Renderer, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
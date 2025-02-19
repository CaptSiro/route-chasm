<?php

namespace modules\forms\layout\Row;

use core\view\Renderer;
use core\view\View;
use modules\forms\controls\FormControl;
use modules\forms\layout\DynamicLayout;

class Row implements View {
    use Renderer, FormControl, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
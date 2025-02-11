<?php

namespace modules\forms\layout\Row;

use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;
use modules\forms\layout\DynamicLayout;

class Row implements Control {
    use Renderer, FormControl, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
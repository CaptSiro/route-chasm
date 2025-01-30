<?php

namespace modules\forms\layout\Column;

use core\view\Render;
use core\view\TemplateRenderer;
use modules\forms\layout\DynamicLayout;

class Column implements Render {
    use TemplateRenderer, DynamicLayout;

    public function __construct(float $widthPercentage = 1) {
        $this->widthPercentage = $widthPercentage;
    }
}
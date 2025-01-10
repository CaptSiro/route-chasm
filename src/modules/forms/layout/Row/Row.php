<?php

namespace modules\forms\layout\Row;

use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;
use modules\forms\layout\DynamicLayout;

class Row implements Control {
    use TemplateRenderer, FormControl, DynamicLayout;

    public function __construct(float $width) {
        $this->width = $width;
    }
}
<?php

namespace components\core\JsCore;

use core\Render;
use core\TemplateRenderer;

class JsCore implements Render {
    use TemplateRenderer;

    public function getSourceFiles(): array {
        $js = $this->getSource('js');

        return [
            "$js/Impulse.js",
            "$js/NumberRange.js",
        ];
    }
}
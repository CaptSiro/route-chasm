<?php

namespace components\core\jsml;

use core\Singleton;
use core\view\Renderer;
use core\view\View;

class Jsml implements View {
    use Renderer, Singleton;

    protected function getSourceFiles(): array {
        $jsml = $this->getResource();

        return [
            "$jsml/jsml.js",
        ];
    }
}
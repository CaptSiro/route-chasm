<?php

namespace components\jsml;

use core\Singleton;
use core\view\Renderer;
use core\view\ViewTemplate;

class Jsml implements ViewTemplate {
    use Renderer, Singleton;

    protected function getSourceFiles(): array {
        $jsml = $this->getResource();

        return [
            "$jsml/Jsml.js",
        ];
    }
}
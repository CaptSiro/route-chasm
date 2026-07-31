<?php

namespace components;

use core\Singleton;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Jsml implements ViewTemplate {
    use ViewTemplateRenderer, Singleton;

    protected function getSourceFiles(): array {
        $jsml = $this->getResource();

        return [
            "$jsml/Jsml.js",
        ];
    }
}
<?php

namespace modules\jsml;

use core\module\DefaultModule;
use core\Render;
use core\Singleton;
use core\TemplateRenderer;

class Jsml extends DefaultModule implements Render {
    use TemplateRenderer;
    use Singleton;



    protected function getSourceFiles(): array {
        $this->accessibleAfterLoad();
        $jsml = $this->getSource();

        return [
            "$jsml/jsml.js",
        ];
    }
}
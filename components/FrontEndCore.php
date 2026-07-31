<?php

namespace components;

use core\configs\AppConfig;
use core\route\Path;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use function glob;

class FrontEndCore implements ViewTemplate {
    use ViewTemplateRenderer;

    protected function getPublicSources(string $path, string $pattern): array {
        $results = glob(Path::join(AppConfig::getConfig()->getPublicDirectory(), $path, $pattern));
        if ($results === false) {
            return [];
        }

        return $results;
    }
}
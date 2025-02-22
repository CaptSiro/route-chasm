<?php

namespace components\core\FrontEndCore;

use core\config\AppConfig;
use core\path\Path;
use core\view\View;
use core\view\Renderer;

class FrontEndCore implements View {
    use Renderer;

    protected function getPublicSources(string $path, string $pattern): array {
        $results = \glob(Path::join(AppConfig::getConfig()->getPublicDirectory(), $path, $pattern));
        if ($results === false) {
            return [];
        }

        return $results;
    }
}
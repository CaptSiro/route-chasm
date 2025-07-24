<?php

namespace components\core\FrontEndCore;

use core\configs\AppConfig;
use core\route\Path;
use core\view\View;
use core\view\Renderer;
use function glob;

class FrontEndCore implements View {
    use Renderer;

    protected function getPublicSources(string $path, string $pattern): array {
        $results = glob(Path::join(AppConfig::getConfig()->getPublicDirectory(), $path, $pattern));
        if ($results === false) {
            return [];
        }

        return $results;
    }
}
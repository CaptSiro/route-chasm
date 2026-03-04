<?php

namespace components\Lumora\widgets\Ai;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class AiWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WAi";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-robot");
    }

    public function getName(): string {
        return "Ai";
    }

    public function getCategory(): string {
        return "Generation";
    }

    public function getScript(): string {
        return $this->getResource("ai.js");
    }

    public function getStyles(): string {
        return $this->getResource("ai.css");
    }

    public function getDependencies(): array {
        return [];
    }
}
<?php

namespace components\widgets\Decoration;

use components\core\Icon;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class TextDecorationWidget implements Widget {
    use Singleton, ResourceLoader;



    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-flower");
    }

    public function getName(): string {
        return "Text Decoration";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("decoration.js");
    }

    public function getStyles(): string {
        return $this->getResource("decoration.css");
    }

    public function getDependencies(): array {
        return [];
    }
}
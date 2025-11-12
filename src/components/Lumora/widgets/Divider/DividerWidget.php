<?php

namespace components\Lumora\widgets\Divider;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class DividerWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WDivider";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-keyboard_space");
    }

    public function getName(): string {
        return "Divider";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("divider.js");
    }

    public function getStyles(): string {
        return $this->getResource("divider.css");
    }

    public function getDependencies(): array {
        return [];
    }
}
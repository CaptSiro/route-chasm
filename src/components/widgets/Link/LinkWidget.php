<?php

namespace components\widgets\Link;

use components\core\Icon;
use components\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class LinkWidget implements Widget {
    use Singleton, ResourceLoader;



    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-md-web");
    }

    public function getName(): string {
        return "Link";
    }

    public function getCategory(): string {
        return "Text";
    }

    public function getScript(): string {
        return $this->getResource("link.js");
    }

    public function getStyles(): string {
        return $this->getResource("link.css");
    }

    public function getDependencies(): array {
        return [];
    }
}
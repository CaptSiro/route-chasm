<?php

namespace components\Lumora\widgets\Page;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class PageWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WPage";
    }

    public function isVisible(): bool {
        return false;
    }

    public function getIcon(): string {
        return Icon::nf("nf-cod-file");
    }

    public function getName(): string {
        return "Page";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("page.js");
    }

    public function getStyles(): string {
        return $this->getResource("page.css");
    }

    public function getDependencies(): array {
        return [];
    }
}
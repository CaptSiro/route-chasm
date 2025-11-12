<?php

namespace components\Lumora\widgets\List;

use components\core\Icon;
use components\Lumora\widgets\Widget;
use core\ResourceLoader;
use core\Singleton;

class ListWidget implements Widget {
    use Singleton, ResourceLoader;



    public function exportClass(): string {
        return "WList";
    }

    public function isVisible(): bool {
        return true;
    }

    public function getIcon(): string {
        return Icon::nf("nf-cod-list_ordered");
    }

    public function getName(): string {
        return "List";
    }

    public function getCategory(): string {
        return "Layout";
    }

    public function getScript(): string {
        return $this->getResource("list.js");
    }

    public function getStyles(): string {
        return $this->getResource("list.css");
    }

    public function getDependencies(): array {
        return [

        ];
    }
}
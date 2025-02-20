<?php

namespace components\core\BreadCrumbs;

use components\core\BreadCrumb\BreadCrumb;
use core\view\Renderer;
use core\view\View;

class BreadCrumbs implements View {
    use Renderer;

    /**
     * @param array<BreadCrumb> $items
     */
    public function __construct(
        protected array $items
    ) {}

    /**
     * @return array<BreadCrumb>
     */
    public function getItems(): array {
        return $this->items;
    }
}
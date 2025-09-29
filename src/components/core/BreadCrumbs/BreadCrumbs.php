<?php

namespace components\core\BreadCrumbs;

use core\view\Renderer;
use core\view\View;

class BreadCrumbs implements View {
    use Renderer;

    /**
     * @param array<string, string|View> $breadcrumbs url => label
     * @param string $delimitor
     * @return static
     */
    public static function from(array $breadcrumbs, string $delimitor = '>'): static {
        $items = [];

        foreach ($breadcrumbs as $url => $label) {
            $items[] = new BreadCrumb($label, $url);
        }

        return new static($items, $delimitor);
    }



    /**
     * @param array<BreadCrumb> $items
     */
    public function __construct(
        protected array $items,
        protected string $delimitor = '>'
    ) {}

    /**
     * @return array<BreadCrumb>
     */
    public function getItems(): array {
        return $this->items;
    }

    public function add(BreadCrumb $breadcrumb): static {
        $this->items[] = $breadcrumb;
        return $this;
    }
}
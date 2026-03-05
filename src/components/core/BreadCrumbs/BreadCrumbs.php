<?php

namespace components\core\BreadCrumbs;

use core\RouteChasmEnvironment;
use core\view\Renderer;
use core\view\View;

class BreadCrumbs implements View {
    use Renderer;

    /**
     * @param array<string, string|View> $breadcrumbs url => label
     * @param ?string $delimitor
     * @return static
     */
    public static function from(array $breadcrumbs, ?string $delimitor = RouteChasmEnvironment::BREAD_CRUMBS_DELIMITOR): static {
        $items = [];

        foreach ($breadcrumbs as $url => $label) {
            if (!is_string($url)) {
                $items[] = new BreadCrumb($label);
                continue;
            }

            $items[] = new BreadCrumb($label, $url);
        }

        return new static($items, $delimitor);
    }



    /**
     * @param array<BreadCrumb> $items
     */
    public function __construct(
        protected array $items,
        protected ?string $delimitor = null
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

    public function setItemTemplate(string $template): static {
        foreach ($this->items as $item) {
            $item->setTemplate($template);
        }

        return $this;
    }

    public function setDelimitor(?string $delimitor): static {
        $this->delimitor = $delimitor;
        return $this;
    }
}
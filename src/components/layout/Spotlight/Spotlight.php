<?php

namespace components\layout\Spotlight;

use core\view\Renderer;
use core\view\View;

class Spotlight implements View {
    use Renderer;



    protected mixed $visible = null;

    /**
     * @param array<View> $views
     */
    public function __construct(
        protected array $views = []
    ) {
        if (!empty($this->views)) {
            $this->visible = array_key_first($this->views);
        }
    }



    public function add(string $label, View $view): static {
        $this->views[$label] = $view;
        return $this;
    }

    public function show(string $label): static {
        $this->visible = $label;
        return $this;
    }

    public function visibilityClass(string $label): string {
        if ($label !== $this->visible) {
            return '';
        }

        return 'spotlight-visible';
    }
}
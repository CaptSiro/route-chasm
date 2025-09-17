<?php

namespace components\layout\Tabs;

use core\view\Renderer;
use core\view\View;

class Tabs implements View {
    use Renderer;

    protected ?string $selected = null;



    /**
     * @param array<string, View> $tabs
     */
    public function __construct(
        protected array $tabs,
        ?string $selected = null
    ) {
        $this->select($selected);
    }



    public function add(string $label, View $view): static {
        $this->tabs[$label] = $view;
        return $this;
    }

    public function select(?string $label): self {
        if (is_null($label) || !in_array($label, array_keys($this->tabs))) {
            // todo warning
            return $this;
        }

        $this->selected = $label;
        return $this;
    }

    protected function updateSelected(): void {
        if (is_null($this->selected)) {
            return;
        }

        if (!in_array($this->selected, array_keys($this->tabs))) {
            $this->selected = null;
        }
    }

    protected function isSelected(string $label, int $index): bool {
        if (is_null($this->selected)) {
            return $index === 0;
        }

        return $label === $this->selected;
    }
}
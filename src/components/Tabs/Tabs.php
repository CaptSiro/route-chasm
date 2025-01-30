<?php

namespace components\Tabs;

use core\view\Render;
use core\view\TemplateRenderer;

class Tabs implements Render {
    use TemplateRenderer;

    protected ?string $selected = null;



    /**
     * @param array<string, Render> $tabs
     */
    public function __construct(
        protected array $tabs,
        ?string $selected = null
    ) {
        $this->select($selected);
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
<?php

namespace core\view;

trait ContainerTrait {
    protected array $views = [];



    public function add(View|string $view): static {
        $this->views[] = $view;
        return $this;
    }

    public function addAll(array $views): static {
        $this->views = array_merge($this->views, $views);
        return $this;
    }

    public function renderViews(): string {
        return implode(' ', $this->views);
    }



    // Component
    public function setRenderer(Renderer $renderer): static {
        foreach ($this->views as $view) {
            Component::propagateSetRenderer($view, $renderer);
        }

        return parent::setRenderer($renderer);
    }

    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'container' => $this->getClass(),
            'items' => $this->views
        ];
    }
}
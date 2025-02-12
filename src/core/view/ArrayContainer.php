<?php

namespace core\view;

trait ArrayContainer {
    /**
     * @var array<Render>
     */
    protected array $children = [];

    public function addContent(Render $render): static {
        $this->children[] = $render;
        return $this;
    }
}
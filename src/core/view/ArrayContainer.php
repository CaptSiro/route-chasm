<?php

namespace core\view;

trait ArrayContainer {
    /**
     * @var array<View>
     */
    protected array $children = [];

    public function addContent(View $view): static {
        $this->children[] = $view;
        return $this;
    }
}
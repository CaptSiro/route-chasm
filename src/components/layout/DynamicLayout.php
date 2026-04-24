<?php

namespace components\layout;

use core\view\View;

trait DynamicLayout {
    /**
     * @var array<View> $children
     */
    private array $children = [];
    private float $widthPercentage;

    public function add(View $child): static {
        $this->children[] = $child;
        return $this;
    }

    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}
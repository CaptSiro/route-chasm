<?php

namespace components\layout;

use core\view\View;

trait DynamicLayout {
    /**
     * @var array<View|string> $children
     */
    private array $children = [];
    private float $widthPercentage;

    public function add(View|string $child): static {
        $this->children[] = $child;
        return $this;
    }

    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}
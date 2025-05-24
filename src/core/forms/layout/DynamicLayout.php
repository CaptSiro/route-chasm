<?php

namespace core\forms\layout;

use core\view\View;

trait DynamicLayout {
    /**
     * @var array<View> $children
     */
    private array $children;
    private float $widthPercentage;

    public function add(View $control): self {
        $this->children[] = $control;
        return $this;
    }

    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}
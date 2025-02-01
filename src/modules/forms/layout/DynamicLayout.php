<?php

namespace modules\forms\layout;

use core\view\Render;

trait DynamicLayout {
    /**
     * @var Render[] $children
     */
    private array $children;
    private float $widthPercentage;

    public function add(Render $control): self {
        $this->children[] = $control;
        return $this;
    }

    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}
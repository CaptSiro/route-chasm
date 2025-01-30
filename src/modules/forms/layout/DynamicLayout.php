<?php

namespace modules\forms\layout;

use modules\forms\controls\Control;

trait DynamicLayout {
    /**
     * @var Control[] $children
     */
    private array $children;
    private float $widthPercentage;

    public function add(Control $control): self {
        $this->children[] = $control;
        return $this;
    }

    public function getStyle(): string {
        return 'style="width: ' .($this->widthPercentage * 100). '%;"';
    }
}
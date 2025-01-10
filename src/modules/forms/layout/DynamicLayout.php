<?php

namespace modules\forms\layout;

use modules\forms\controls\Control;

trait DynamicLayout {
    /**
     * @var Control[] $children
     */
    private array $children;
    private float $width;

    public function add(Control $control): self {
        $this->children[] = $control;
        return $this;
    }

    public function getWidthPercentage(): float {
        return (1 / $this->width) * 100;
    }

    public function getStyle(): string {
        return 'style="width: ' .$this->getWidthPercentage(). '%;"';
    }
}
<?php

namespace components\layout\Grid\Loader;

trait GridPortion {
    protected int $portionSize;

    public function setPortionSize(int $size): static {
        $this->portionSize = $size;
        return $this;
    }
}
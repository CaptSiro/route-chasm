<?php

namespace components\layout\Grid\Loader;

use components\core\PaginationControl\Portion;

trait GridPortion {
    use Portion;

    protected int $portionSize;

    public function setPortionSize(int $size): static {
        $this->portionSize = $size;
        return $this;
    }
}
<?php

namespace components\core\Pagination;

use core\view\Component;

class PaginationControl extends Component implements Pagination {
    public function __construct(
        protected int $current = 0,
        protected int $max = 0,
        protected ?PaginationUrlCreator $creator = null,
        bool $isMiddleware = false
    ) {
        parent::__construct($isMiddleware);
    }



    public function setCurrent(int $current): static {
        $this->current = $current;
        return $this;
    }

    public function setMax(int $max): static {
        $this->max = $max;
        return $this;
    }

    public function setUrlCreator(PaginationUrlCreator $creator): static {
        $this->creator = $creator;
        return $this;
    }
}
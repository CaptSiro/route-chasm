<?php

namespace components\layout\Pagination;

use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class PaginationControl extends Component implements Pagination {
    public function __construct(
        protected int $current = 0,
        protected int $max = 0,
        protected ?PaginationUrlCreator $creator = null,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
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
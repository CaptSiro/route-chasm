<?php

namespace components\core\Pagination;

interface Pagination {
    public function setCurrent(int $current): static;

    public function setMax(int $max): static;

    public function setUrlCreator(PaginationUrlCreator $creator): static;
}
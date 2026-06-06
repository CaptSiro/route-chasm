<?php

namespace components\layout\Grid\Loader;

interface GridPortionLoader extends GridLoader {
    public function setPortionSize(int $size): static;
}
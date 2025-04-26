<?php

namespace components\layout\Grid\PagedGrid;

use components\layout\Grid\Grid;
use core\view\Renderer;
use core\view\View;

class PagedGrid implements View {
    use Renderer;

    protected Grid $grid;
    protected string $query;
}
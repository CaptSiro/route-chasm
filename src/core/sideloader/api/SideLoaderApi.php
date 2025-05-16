<?php

namespace core\sideloader\api;

use core\view\Renderer;
use core\view\View;

class SideLoaderApi implements View {
    use Renderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
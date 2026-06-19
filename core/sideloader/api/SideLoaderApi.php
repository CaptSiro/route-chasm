<?php

namespace core\sideloader\api;

use core\view\Renderer;
use core\view\ViewTemplate;

class SideLoaderApi implements ViewTemplate {
    use Renderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
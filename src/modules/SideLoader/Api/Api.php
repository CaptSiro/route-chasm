<?php

namespace modules\SideLoader\Api;

use core\view\Render;
use core\view\Renderer;

class Api implements Render {
    use Renderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
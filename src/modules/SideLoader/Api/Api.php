<?php

namespace modules\SideLoader\Api;

use core\view\View;
use core\view\Renderer;

class Api implements View {
    use Renderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
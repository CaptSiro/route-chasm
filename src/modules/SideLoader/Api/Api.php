<?php

namespace modules\SideLoader\Api;

use core\view\Render;
use core\view\TemplateRenderer;

class Api implements Render {
    use TemplateRenderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
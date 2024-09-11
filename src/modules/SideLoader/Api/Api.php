<?php

namespace modules\SideLoader\Api;

use core\Render;
use core\TemplateRenderer;

class Api implements Render {
    use TemplateRenderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
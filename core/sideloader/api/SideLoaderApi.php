<?php

namespace core\sideloader\api;

use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class SideLoaderApi implements ViewTemplate {
    use ViewTemplateRenderer;

    public function __construct(
        protected string $importUrl
    ) {}
}
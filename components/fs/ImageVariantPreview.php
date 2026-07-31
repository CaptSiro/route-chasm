<?php

namespace components\fs;

use core\ResourceLoader;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class ImageVariantPreview implements ViewTemplate {
    use ViewTemplateRenderer, ResourceLoader;



    public function __construct(
        protected string $widthSelector,
        protected string $heightSelector,
    ) {}
}
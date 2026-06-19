<?php

namespace components\fs;

use core\ResourceLoader;
use core\view\Renderer;
use core\view\ViewTemplate;

class ImageVariantPreview implements ViewTemplate {
    use Renderer, ResourceLoader;



    public function __construct(
        protected string $widthSelector,
        protected string $heightSelector,
    ) {}
}
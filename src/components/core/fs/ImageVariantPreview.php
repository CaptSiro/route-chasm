<?php

namespace components\core\fs;

use core\ResourceLoader;
use core\view\Renderer;
use core\view\View;

class ImageVariantPreview implements View {
    use Renderer, ResourceLoader;



    public function __construct(
        protected string $widthSelector,
        protected string $heightSelector,
    ) {}
}
<?php

namespace components\Admin;

use core\ResourceLoader;
use core\view\Renderer;
use core\view\ViewTemplate;
use models\fs\Directory;

class AdminFileSystemCreateDirectory implements ViewTemplate {
    use Renderer, ResourceLoader;

    public function __construct(
        protected Directory $directory
    ) {}
}
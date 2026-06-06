<?php

namespace components\Admin\FileSystem;

use core\ResourceLoader;
use core\view\Renderer;
use core\view\View;
use models\fs\Directory;

class AdminFileSystemCreateDirectory implements View {
    use Renderer, ResourceLoader;

    public function __construct(
        protected Directory $directory
    ) {}
}
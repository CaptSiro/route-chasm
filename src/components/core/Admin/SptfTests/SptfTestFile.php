<?php

namespace components\core\Admin\SptfTests;

use core\view\Renderer;
use core\view\View;
use sptf\structs\TestFile;

class SptfTestFile implements View {
    use Renderer;

    public function __construct(
        protected TestFile $testFile
    ) {}
}
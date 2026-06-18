<?php

namespace components\Admin\SptfTests;

use core\tf\TestFile;
use core\view\Renderer;
use core\view\View;

class SptfTestFile implements View {
    use Renderer;

    public function __construct(
        protected TestFile $testFile
    ) {}
}
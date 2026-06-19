<?php

namespace components\Admin\SptfTests;

use core\tf\TestFile;
use core\view\Renderer;
use core\view\ViewTemplate;

class SptfTestFile implements ViewTemplate {
    use Renderer;

    public function __construct(
        protected TestFile $testFile
    ) {}
}
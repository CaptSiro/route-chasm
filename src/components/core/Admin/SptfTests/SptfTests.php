<?php

namespace components\core\Admin\SptfTests;

use core\view\ContainerContent;
use sptf\Sptf;

class SptfTests extends ContainerContent {
    public function __construct(
        protected string $directory
    ) {
        parent::__construct();
    }

    /**
     * @return array<SptfTestFile>
     */
    public function getTestFiles(): array {
        return array_map(
            fn($x) => new SptfTestFile($x),
            Sptf::evaluateDirectory($this->directory)
        );
    }
}
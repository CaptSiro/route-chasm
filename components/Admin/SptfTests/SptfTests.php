<?php

namespace components\Admin\SptfTests;

use core\sptf\Sptf;
use core\view\ContainerContent;

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
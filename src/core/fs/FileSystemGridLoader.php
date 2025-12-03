<?php

namespace core\fs;

use components\layout\Grid\GridLayout;
use components\layout\Grid\Loader\GridLoader;
use models\core\fs\Directory;

class FileSystemGridLoader implements GridLoader {
    public function __construct(
        protected Directory $directory
    ) {}



    public function load(GridLayout $context): array {
        return array_merge(
            $this->directory->getSubDirectories(),
            $this->directory->getFiles()
        );
    }
}
<?php

namespace core\fs;

use components\layout\Grid\GridLayout;
use components\layout\Grid\Loader\GridLoader;
use models\core\fs\Directory;

class FileSystemGridLoader implements GridLoader {
    public function __construct(
        protected Directory $directory,
        protected ?string $fileType = null
    ) {}



    public function load(GridLayout $context): array {
        $files = is_null($this->fileType)
            ? $this->directory->getFiles()
            : $this->directory->filterFiles($this->fileType);

        return array_merge(
            $this->directory->getSubDirectories(),
            $files
        );
    }
}
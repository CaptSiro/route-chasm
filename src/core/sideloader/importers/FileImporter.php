<?php

namespace core\sideloader\importers;

use core\view\Renderer;
use core\view\View;

class FileImporter implements View {
    use Renderer;

    protected array $files;



    public function __construct(
        protected string $fileType = "text/plain",
    ) {}



    public function setFiles(array $files): self {
        $this->files = $files;
        return $this;
    }

    public function getFileType(): string {
        return $this->fileType;
    }

    public function setFileType(string $fileType): self {
        $this->fileType = $fileType;
        return $this;
    }
}
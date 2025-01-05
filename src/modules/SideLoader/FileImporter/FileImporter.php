<?php

namespace modules\SideLoader\FileImporter;

use core\view\Render;
use core\view\TemplateRenderer;

class FileImporter implements Render {
    use TemplateRenderer;

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
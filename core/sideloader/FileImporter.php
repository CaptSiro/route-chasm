<?php

namespace core\sideloader;

trait FileImporter {
    protected array $files;

    public function setFiles(array $files): static {
        $this->files = array_unique($files);
        return $this;
    }
}
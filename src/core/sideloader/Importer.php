<?php

namespace core\sideloader;

interface Importer {
    public function setFiles(array $files): static;

    public function getFileExtension(): string;

    public function getFileMimeType(): string;

    public function fileHead(string $file): ?string;

    public function begin(): ?string;

    public function end(): ?string;
}
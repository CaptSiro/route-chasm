<?php

namespace core\fs;

use core\url\Url;
use models\core\fs\Directory;

interface FileSystemEntry {
    public function getEntryName(): string;

    public function getEntryIcon(): string;

    public function renameEntry(string $name): static;

    public function createRenameEntryUrl(): Url;

    public function deleteEntry(): void;

    public function createDeleteEntryUrl(): Url;

    public function moveEntry(Directory $destination): static;

    public function getParent(): ?Directory;

    /**
     * @return array<Directory>
     */
    public function getParents(): array;
}
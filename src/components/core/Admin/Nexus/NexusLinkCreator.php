<?php

namespace components\core\Admin\Nexus;

interface NexusLinkCreator {
    public function getCreateLink(string $path): string;

    public function getUpdateLink(string $path, mixed $id): string;

    public function getDeleteLink(string $path, mixed $id): string;
}
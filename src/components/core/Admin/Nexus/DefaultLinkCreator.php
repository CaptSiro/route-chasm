<?php

namespace components\core\Admin\Nexus;

use core\route\Path;
use core\Singleton;

class DefaultLinkCreator implements NexusLinkCreator {
    use Singleton;

    public function getCreateLink(string $path): string {
        return $path;
    }

    public function getUpdateLink(string $path, mixed $id): string {
        return Path::join($path, (string) $id);
    }

    public function getDeleteLink(string $path, mixed $id): string {
        return Path::join($path, (string) $id);
    }
}
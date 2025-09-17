<?php

namespace components\core\Admin\Nexus;

use core\route\Path;
use core\url\Url;

interface NexusLinkCreator {
    public function getCreateUrl(Path $path): Url;

    public function getUpdateUrl(Path $path, mixed $id): Url;

    public function getDeleteUrl(Path $path, mixed $id): Url;
}
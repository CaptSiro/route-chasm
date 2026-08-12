<?php

namespace components\nexus;

use core\route\Path;
use core\url\Url;

interface NexusUrlCreator {
    public function getCreateUrl(Path $path): Url;

    public function getUpdateUrl(Path $path, mixed $id): Url;

    public function getDeleteUrl(Path $path, mixed $id): Url;
}
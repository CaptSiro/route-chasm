<?php

namespace core\cache;

use RuntimeException;

class FileAccessException extends RuntimeException {
    public function __construct(
        protected readonly string $path
    ) {
        parent::__construct("Can not access file: '$path'");
    }
}
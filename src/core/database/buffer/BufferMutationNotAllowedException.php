<?php

namespace core\database\buffer;

use RuntimeException;

class BufferMutationNotAllowedException extends RuntimeException {
    public function __construct() {
        parent::__construct("Mutation of buffer is not supported");
    }
}
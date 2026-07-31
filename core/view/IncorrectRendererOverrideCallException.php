<?php

namespace core\view;

use RuntimeException;

class IncorrectRendererOverrideCallException extends RuntimeException {
    public function __construct(
        string $expected,
        string $received
    ) {
        parent::__construct("Expected class $expected, received $received");
    }
}
<?php

namespace core\view;

use RuntimeException;

class InvalidPropertyException extends RuntimeException {
    public function __construct(string $property, string $type) {
        parent::__construct("Property '$property' must be type of '$type'");
    }
}
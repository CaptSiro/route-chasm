<?php

namespace core\dictionary;

use RuntimeException;

class NotDefinedException extends RuntimeException {
    public function __construct(
        protected readonly string $property,
    ) {
        parent::__construct("Property '$this->property' is not defined");
    }

    /**
     * @return string
     */
    public function getProperty(): string {
        return $this->property;
    }
}
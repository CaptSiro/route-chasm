<?php

namespace core;

use RuntimeException;

class DoesNotExistException extends RuntimeException {
    public function __construct(
        string $message,
        protected readonly mixed $subject
    ) {
        parent::__construct($message);
    }

    /**
     * @return mixed
     */
    public function getSubject(): mixed {
        return $this->subject;
    }
}
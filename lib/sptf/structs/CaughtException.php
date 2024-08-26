<?php

namespace sptf\structs;

use Exception;
use sptf\interfaces\Assertion;
use sptf\interfaces\Html;

class CaughtException extends Interrupt implements Assertion {
    public function __construct(
        protected Exception $exception
    ) {
        parent::__construct(
            get_class($this->exception),
            $this->exception->getMessage(),
            $this->exception->getTrace()
        );
    }



    function result(): bool {
        return false;
    }

    function error(): Html {
        return $this;
    }
}
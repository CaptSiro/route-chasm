<?php

namespace core\sptf\structs;

use core\sptf\interfaces\Assertion;
use core\sptf\interfaces\Html;
use Exception;

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
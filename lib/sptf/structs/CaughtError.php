<?php

namespace sptf\structs;

use Error;
use sptf\interfaces\Assertion;
use sptf\interfaces\Html;

class CaughtError extends Interrupt implements Assertion {
    public function __construct(
        protected Error $error
    ) {
        parent::__construct(
            get_class($this->error),
            $this->error->getMessage(),
            $this->error->getTrace()
        );
    }



    function result(): bool {
        return false;
    }

    function error(): Html {
        return $this;
    }
}
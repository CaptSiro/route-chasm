<?php

namespace components\tf;

use core\tf\Assertion;
use core\view\View;
use Error;

class CaughtError extends Interrupt implements Assertion {
    public function __construct(
        protected Error $error
    ) {
        parent::__construct(
            get_class($this->error),
            $this->error->getMessage(),
            $this->error->getTrace()
        );

        $this->setTemplate(Interrupt::getTemplateResourceStatic());
    }



    function result(): bool {
        return false;
    }

    function error(): View {
        return $this;
    }
}
<?php

namespace components\tf;

use core\tf\Assertion;
use core\view\View;
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

        $this->setTemplate(Interrupt::getTemplateResourceStatic());
    }



    function result(): bool {
        return false;
    }

    function error(): View {
        return $this;
    }
}
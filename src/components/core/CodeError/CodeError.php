<?php

namespace components\core\CodeError;

use core\Component;

class CodeError extends Component {
    public function __construct(
        protected string $message,
        protected int $code
    ) {}
}
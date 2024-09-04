<?php

namespace core\endpoints;

use Closure;
use core\Request;
use core\Response;

class Procedure implements Endpoint {
    use SimpleEndpoint;



    public function __construct(
        protected readonly Closure $function,
        protected bool $isMiddleware = false
    ) {}



    public function middleware(): self {
        $this->isMiddleware = true;
        return $this;
    }

    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    function call(Request $request, Response $response): void {
        ($this->function)($request, $response);
    }
}
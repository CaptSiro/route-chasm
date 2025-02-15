<?php

namespace core\endpoints;

use Closure;
use core\communication\Request;
use core\communication\Response;

class Procedure implements Endpoint {
    use SimpleEndpoint;

    public static function middleware(Closure $function): static {
        return new static($function, true);
    }



    public function __construct(
        protected readonly Closure $function,
        protected bool $isMiddleware = false
    ) {}



    public function setIsMiddleware(bool $isMiddleware): static {
        $this->isMiddleware = $isMiddleware;
        return $this;
    }

    public function isMiddleware(): bool {
        return $this->isMiddleware;
    }

    function execute(Request $request, Response $response): void {
        ($this->function)($request, $response);
    }
}
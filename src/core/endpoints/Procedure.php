<?php

namespace core\endpoints;

use Closure;
use core\Request;
use core\Response;

class Procedure implements Endpoint {
    use SimpleEndpoint;



    public function __construct(
        private readonly Closure $function
    ) {}



    function call(Request $request, Response $response): void {
        ($this->function)($request, $response);
    }
}
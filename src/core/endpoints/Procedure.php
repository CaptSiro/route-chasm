<?php

namespace core\endpoints;

use Closure;
use core\Request;

class Procedure implements Endpoint {
    use SimpleEndpoint;



    public function __construct(
        private readonly Closure $function
    ) {}



    function call(Request $request): void {
        ($this->function)($request);
    }
}
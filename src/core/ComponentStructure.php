<?php

namespace core;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;

class ComponentStructure implements Render, Endpoint {
    use SimpleEndpoint;



    public function __construct(
        protected Render $root
    ) {}



    function call(Request $request, Response $response): void {
        $response->render($this);
    }

    function render(?string $template = null): string {
        return $this->root->render();
    }
}
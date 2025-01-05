<?php

namespace core\view;

use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;

class ComponentStructure implements Render, Endpoint {
    use SimpleEndpoint;



    public function __construct(
        protected Render $root
    ) {}



    public function isMiddleware(): bool {
        return false;
    }

    function execute(Request $request, Response $response): void {
        $response->render($this);
    }

    function render(?string $template = null): string {
        return $this->root->render();
    }
}
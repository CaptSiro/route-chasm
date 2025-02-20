<?php

namespace core\view;

use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;

class ComponentStructure implements View, Endpoint {
    use Renderer, SimpleEndpoint;



    public function __construct(
        protected View $root
    ) {}



    public function isMiddleware(): bool {
        return false;
    }

    function execute(Request $request, Response $response): void {
        $response->renderRoot($this->root);
    }

    function render(): string {
        return $this->root->render();
    }
}
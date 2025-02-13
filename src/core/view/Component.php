<?php

namespace core\view;

use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;

class Component implements View, Endpoint {
    use Renderer, SimpleEndpoint;



    public function __toString(): string {
        return $this->render();
    }

    public function isMiddleware(): bool {
        return false;
    }

    public function execute(Request $request, Response $response): void {
        $response->render($this);
    }
}
<?php

namespace core;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use core\utils\Files;

class Component implements Render, Endpoint {
    use SimpleEndpoint;
    use TemplateRenderer;



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
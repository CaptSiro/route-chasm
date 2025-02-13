<?php

namespace core\view;

use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use JsonSerializable;

class JsonComponent implements View, Endpoint, JsonSerializable {
    use SimpleEndpoint;

    protected mixed $json;



    public function __construct(mixed $json = null) {
        $this->json = $json;
    }



    public function isMiddleware(): bool {
        return false;
    }

    public function json(): null {
        return null;
    }

    function execute(Request $request, Response $response): void {
        $response->json($this);
    }

    function render(?string $template = null): string {
        return json_encode($this);
    }

    public function jsonSerialize(): mixed {
        return $this->json ?? $this->json();
    }

    public function __toString(): string {
        return json_encode($this);
    }
}
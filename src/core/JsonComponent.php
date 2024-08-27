<?php

namespace core;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use JsonSerializable;

class JsonComponent implements Render, Endpoint, JsonSerializable {
    use SimpleEndpoint;

    protected mixed $json;



    public function __construct(mixed $json = null) {
        $this->json = $json;
    }



    public function json(): mixed {
        return null;
    }

    function call(Request $request, Response $response): void {
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
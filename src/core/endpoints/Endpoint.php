<?php

namespace core\endpoints;

use core\Request;
use core\Response;
use core\tree\Node;
use core\Url;

interface Endpoint {
    public function getNode(): Node;

    public function setNode(Node $node);

    public function getUrl(): Url;

    public function isMiddleware(): bool;

    public function call(Request $request, Response $response): void;
}
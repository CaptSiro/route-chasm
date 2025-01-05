<?php

namespace core\endpoints;

use core\communication\Request;
use core\communication\Response;
use core\tree\Node;
use core\url\Url;

interface Endpoint {
    public function getNode(): Node;

    public function setNode(Node $node);

    public function getUrl(): Url;

    public function isMiddleware(): bool;

    public function execute(Request $request, Response $response): void;

    public function __toString(): string;
}
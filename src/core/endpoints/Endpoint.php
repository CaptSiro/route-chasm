<?php

namespace core\endpoints;

use core\Request;
use core\tree\Node;
use core\Url;

interface Endpoint {
    function getNode(): Node;

    function setNode(Node $node);

    function getUrl(): Url;

    function call(Request $request): void;
}
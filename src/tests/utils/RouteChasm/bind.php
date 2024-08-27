<?php

use core\endpoints\Handler;
use core\Http;
use core\path\parser\Parser;
use core\Router;
use sptf\Sptf;

function bind(string $path, Handler ...$handlers): Router {
    if (empty($handlers)) {
        $handlers = [
            Http::get(fn() => 0),
            Http::post(fn() => 0)
        ];
    }

    $r = new Router();

    $r->use($path, ...$handlers);
    $node = $r
        ->getNode()
        ->walk(Parser::parse($path));

    if ($node === null) {
        Sptf::fail("Failed to find node for path: '$path'");
        return $r;
    }

    Sptf::expect(count($node->getEndpoints()))->toBe(2);

    return $r;
}
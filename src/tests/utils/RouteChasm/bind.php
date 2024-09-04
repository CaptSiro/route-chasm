<?php

use core\http\Http;
use core\http\HttpGate;
use core\path\parser\Parser;
use core\Router;
use sptf\Sptf;

function bind(string $path): Router {
    $r = new Router();

    $r->use($path, Http::get(fn() => 0), Http::post(fn() => 0));
    $node = $r->findPath($path);

    if ($node === null) {
        Sptf::fail("Failed to find node for path: '$path'");
        return $r;
    }

    Sptf::expect(count($node->getEndpoints()))->toBe(2);

    return $r;
}
<?php

use core\tree\SnapshotStack;
use core\utils\Arrays;
use sptf\Sptf;

Sptf::test("merges match arrays", function () {
    $stack = new SnapshotStack();

    $stack->push([
        "greeting" => "hello",
    ], []);
    $stack->push([
        "name" => "John",
    ], []);
    $stack->push([
        "id" => "69420",
    ], []);

    $trail = $stack->merge();
    Sptf::expect($trail->getParams())->toBe([
        "greeting" => "hello",
        "name" => "John",
        "id" => "69420",
    ])->compare(fn($a, $b) => Arrays::equal($a, $b));
});
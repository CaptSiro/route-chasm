<?php

use core\tree\traversable\MatchStack;
use core\utils\Arrays;
use sptf\Sptf;

Sptf::test("merges match arrays", function () {
    $stack = new MatchStack();

    $stack->push([
        "greeting" => "hello",
    ], []);
    $stack->push([
        "name" => "John",
    ], []);
    $stack->push([
        "id" => "69420",
    ], []);

    $stack->merge($matches, $x);
    Sptf::expect($matches)->toBe([
        "greeting" => "hello",
        "name" => "John",
        "id" => "69420",
    ])->compare(fn($a, $b) => Arrays::equal($a, $b));
});
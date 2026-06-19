<?php

use core\route\Path;
use core\tf\Test;
use core\tf\Unit;



Test::case("should return correct depth", function () {
    $paths = [
        "" => 0,
        "/" => 0,
        "foo" => 1,
        "/foo" => 1,
        "foo/bar" => 2,
        "/foo/bar" => 2,
        "/@[foo]/[bar]/fizz" => 3,
    ];

    foreach ($paths as $path => $depth) {
        Unit::expect(Path::depth($path))
            ->toBe($depth);
    }
});

// todo
//  - Path::join(...)
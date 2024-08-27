<?php

use core\utils\Arrays;
use core\utils\Files;
use core\utils\Strings;
use sptf\Sptf;



Sptf::test("finds all occurrences using Strings::positions", function () {
    $needle = "pos";
    $haystack = "pos       pos  pos       pos";

    $positions = Strings::positions($needle, $haystack, 0);

    Sptf::expect(count($positions))->toBe(4);

    foreach ($positions as $position) {
        Sptf::expect(substr($haystack, $position, strlen($needle)))->toBe($needle);
    }
});



Sptf::test("match arrays", function () {
    Sptf::expect(Arrays::equal([], []))->toBe(true);
    Sptf::expect(Arrays::equal([1], [1]))->toBe(true);
    Sptf::expect(Arrays::equal([1], []))->toBe(false);
    Sptf::expect(Arrays::equal(["foo" => "bar"], ["foo" => "bar"]))->toBe(true);
    Sptf::expect(Arrays::equal(["foo" => "bar"], ["bar" => "foo"]))->toBe(false);
});



Sptf::test("reverse array", function () {
    $array = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    Sptf::expect([...Arrays::reversed($array)])
        ->compare(fn($a, $b) => Arrays::equal($a, $b))
        ->toBe(array_reverse($array));
});


Sptf::test("get file extensions based only on name", function () {
    Sptf::expect(Files::extension("."))
        ->toBe("");

    foreach (["", "no-ext", "path/with/no/ext"] as $path) {
        Sptf::expect(Files::extension($path))
            ->toBe(null);
    }

    foreach ([".txt", "with-ext.txt", "path/with/ext.txt"] as $path) {
        Sptf::expect(Files::extension($path))
            ->toBe("txt");
    }
});
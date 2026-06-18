<?php

use core\tf\Test;
use core\tf\Unit;
use core\utils\Arrays;
use core\utils\Files;
use core\utils\Strings;



Test::case("finds all occurrences using Strings::positions", function () {
    $needle = "pos";
    $haystack = "pos       pos  pos       pos";

    $positions = Strings::positions($needle, $haystack, 0);

    Unit::expect(count($positions))->toBe(4);

    foreach ($positions as $position) {
        Unit::expect(substr($haystack, $position, strlen($needle)))->toBe($needle);
    }
});



Test::case("match arrays", function () {
    Unit::expect(Arrays::equal([], []))->toBe(true);
    Unit::expect(Arrays::equal([1], [1]))->toBe(true);
    Unit::expect(Arrays::equal([1], []))->toBe(false);
    Unit::expect(Arrays::equal(["foo" => "bar"], ["foo" => "bar"]))->toBe(true);
    Unit::expect(Arrays::equal(["foo" => "bar"], ["bar" => "foo"]))->toBe(false);
});



Test::case("reverse array", function () {
    $array = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    Unit::expect([...Arrays::reversed($array)])
        ->compare(fn($a, $b) => Arrays::equal($a, $b))
        ->toBe(array_reverse($array));
});


Test::case("get file extensions based only on name", function () {
    Unit::expect(Files::extension("."))
        ->toBe("");

    foreach (["", "no-ext", "path/with/no/ext"] as $path) {
        Unit::expect(Files::extension($path))
            ->toBe(null);
    }

    foreach ([".txt", "with-ext.txt", "path/with/ext.txt"] as $path) {
        Unit::expect(Files::extension($path))
            ->toBe("txt");
    }
});
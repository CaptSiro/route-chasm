<?php

use core\route\compiler\RouteCompiler;
use core\tf\Test;
use core\tf\Unit;



Test::case("parse valid identifiers", function () {
    $pass = true;
    $compiler = new RouteCompiler();
    $idents = ["a", "A", "foo", "bar1"];

    foreach ($idents as $ident) {
        if (!$compiler->isIdentValid($ident)) {
            Unit::fail("Should parse: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        Unit::pass();
    }
});



Test::case("find invalid identifiers", function () {
    $pass = true;
    $compiler = new RouteCompiler();
    $idents = ["", "1", "foo-bar", "foo!", "你好"];

    foreach ($idents as $ident) {
        if ($compiler->isIdentValid($ident)) {
            Unit::fail("Should invalidate: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        Unit::pass();
    }
});
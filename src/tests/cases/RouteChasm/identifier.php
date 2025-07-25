<?php

use core\route\compiler\RouteCompiler;
use sptf\Sptf;



Sptf::test("parse valid identifiers", function () {
    $pass = true;
    $compiler = new RouteCompiler();
    $idents = ["a", "A", "foo", "bar1"];

    foreach ($idents as $ident) {
        if (!$compiler->isIdentValid($ident)) {
            Sptf::fail("Should parse: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        Sptf::pass();
    }
});



Sptf::test("find invalid identifiers", function () {
    $pass = true;
    $compiler = new RouteCompiler();
    $idents = ["", "1", "foo-bar", "foo!", "你好"];

    foreach ($idents as $ident) {
        if ($compiler->isIdentValid($ident)) {
            Sptf::fail("Should invalidate: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        Sptf::pass();
    }
});
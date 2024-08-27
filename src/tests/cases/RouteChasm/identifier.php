<?php

use core\path\parser\Ident;
use sptf\Sptf;



Sptf::test("parse valid identifiers", function () {
    $pass = true;
    $idents = ["_", "a", "A", "_asdf_ASDF_1234", "_1", "name"];

    foreach ($idents as $ident) {
        if (!Ident::validate($ident)) {
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
    $idents = ["", "1", "foo-bar", "foo!", "你好"];

    foreach ($idents as $ident) {
        if (Ident::validate($ident)) {
            Sptf::fail("Should invalidate: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        Sptf::pass();
    }
});
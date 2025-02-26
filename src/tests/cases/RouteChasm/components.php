<?php

use core\view\Json;
use sptf\Sptf;
use tests\utils\RouteChasm\TestComponent\TestComponent;

Sptf::test("component should render correct template", function () {
    $str = "foo";
    $c = new TestComponent($str);

    Sptf::expect(trim((string) $c))
        ->toBe($str);

    Sptf::expect($c->renderTemplated($c->getResource("./TestComponentUpperCase")))
        ->toBe(strtoupper($str));
});

Sptf::test("serialize JsonComponent", function () {
    $data = ["error" => 418, "message" => "I'm a teapot"];
    $json = new Json($data);

    Sptf::expect((string) $json)
        ->toBe(json_encode($data));
});
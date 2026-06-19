<?php

use core\tf\Test;
use core\tf\Unit;
use core\view\Json;
use tests\utils\TestComponent\TestComponent;

Test::case("component should render correct template", function () {
    $str = "foo";
    $c = new TestComponent($str);

    Unit::expect(trim((string) $c))
        ->toBe($str);

    Unit::expect($c->renderTemplated($c->getResource("./TestComponentUpperCase")))
        ->toBe(strtoupper($str));
});

Test::case("serialize JsonComponent", function () {
    $data = ["error" => 418, "message" => "I'm a teapot"];
    $json = new Json($data);

    Unit::expect((string) $json)
        ->toBe(json_encode($data));
});
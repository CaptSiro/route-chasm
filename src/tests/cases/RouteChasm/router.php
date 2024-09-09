<?php

use core\App;
use core\http\Http;
use core\path\Path;
use core\Request;
use core\Resource;
use core\Response;
use core\Router;
use core\tree\Trail;
use core\utils\Arrays;
use patterns\Ident;
use patterns\Number;
use sptf\Sptf;
use tests\utils\RouteChasm\TestResource;

require_once __DIR__ ."/../../utils/RouteChasm/bind.php";
$compare = fn($a, $b) => Arrays::equal($a, $b);



Sptf::test("binds handlers to self", function () {
    $r = new Router();

    $r->use("/",
        Http::get(fn() => 0),
        Http::post(fn() => 0)
    );

    $endpoints = $r->getEndpoints();
    Sptf::expect(count($endpoints))->toBe(2);
});



Sptf::test("binds handlers to direct child node", function () {
    bind("/child");
});



Sptf::test("binds handlers to distant child node", function () {
    bind("/some/path/to/child");
});



Sptf::test("finds all leaf nodes", function () use ($compare) {
    $r = new Router();

    $r->use("/[id]/[bar]", Http::get(fn() => 0));
    $r->use("/[id]/[foo]", Http::get(fn() => 0));

    $trail = $r->findPath("/69/any");

    Sptf::expect(is_null($trail))->toBe(false);
    Sptf::expect($trail->getParams())->toBe([
        "id" => "69",
        "bar" => "any"
    ])->compare($compare);
});



Sptf::test("skip typed search", function () use ($compare) {
    $r = new Router();

    $r->use("/[id]/[bar]",
        Http::get(fn() => 0)
    );
    $r->use(
        Path::from("/[id]/[foo]")
            ->param("id", Number::getInstance()),
        Http::get(fn() => 0)
    );

    $trail = $r->findPath("/foo/any");

    Sptf::expect(is_null($trail))->toBe(false);

    Sptf::expect($trail->getParams())->toBe([
        "id" => "foo",
        "bar" => "any"
    ])->compare($compare);
});



Sptf::test("find path for deeply nested Routers", function () {
    $r0 = new Router();
    $r1 = new Router();

    $r0->bind("/a", $r1);
    $r1->use("/b", fn() => 0);

    Sptf::expect(is_null($r0->findPath("/a/b")))
        ->toBe(false);
});

Sptf::test("find path for deeply nested dynamic Routers", function () {
    $r2 = new Router();
    $r3 = new Router();

    $ret = [];

    $atUser = Path::from("/@[user]")
        ->param("user", Ident::getInstance());
    $r2->use(
        $atUser,
        function () use (&$ret) {
            $ret[] = 0;
        }
    );
    $r2->bind($atUser, $r3);

    $r3->use(
        "/",
        function () use (&$ret) {
            $ret[] = 1;
        },
    );

    $r3->use(
        Path::from("/post-[post_id]")
            ->param("post_id", Number::getInstance()),
        function () use (&$ret) {
            $ret[] = 2;
        },
    );

    $request = Request::test();
    $trail = $r2->findPath("/@CaptSiro/post-420");
    Sptf::expect(is_null($trail))
        ->toBe(false);

    foreach ($trail->getEndpoints() as $endpoint) {
        $endpoint->execute($request, new Response());
    }

    Sptf::expect($ret)
        ->toBe([0, 1, 2])
        ->compare(fn($a, $b) => Arrays::equal($a, $b));
});



Sptf::test("get correct url path for Resource", function () {
    $r0 = new Router();
    $r1 = new Router();

    $t0 = new TestResource();
    $t1 = new TestResource();

    $r0->bind(
        "/a",
        $r1
    );

    $r0->resource(
        "/",
        $t0
    );

    $r1->resource(
        "/b",
        $t1
    );

    App::getInstance()
        ->options
        ->set(App::OPTION_DO_ADD_HOME_TO_URL_PATH, false);

    Sptf::expect($t0->getUrl(Resource::URL_READ))
        ->toBe("/[unique]");

    Sptf::expect($t1->getUrl(Resource::URL_READ))
        ->toBe("/a/b/[unique]");


    App::getInstance()
        ->options
        ->set(App::OPTION_DO_ADD_HOME_TO_URL_PATH, true);
});

Sptf::test("handle any terminated paths", function () {
    $r0 = new Router();

    $r0->use(
        "/public/**",
        fn() => 0
    );

    Sptf::expect(is_null($r0->findPath("/public/css/styles.css")))
        ->toBe(false);
});
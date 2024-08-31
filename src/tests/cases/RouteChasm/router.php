<?php

use core\Http;
use core\path\Path;
use core\Request;
use core\Resource;
use core\Response;
use core\Router;
use core\tree\traversable\FoundNode;
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

    $handlers = $r->getNode()->getEndpoints();
    Sptf::expect(count($handlers))->toBe(2);
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

    $found = $r->findPath("/69/any");

    Sptf::expect(count($found))->toBe(2);

    Sptf::expect($found[0]->matches)->toBe([
        "id" => "69",
        "bar" => "any"
    ])->compare($compare);

    Sptf::expect($found[1]->matches)->toBe([
        "id" => "69",
        "foo" => "any"
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

    $o = $r->findPath("/foo/any");

    Sptf::expect(count($o))->toBe(1);

    Sptf::expect($o[0]->matches)->toBe([
        "id" => "foo",
        "bar" => "any"
    ])->compare($compare);
});



Sptf::test("find path for deeply nested Routers", function () {
    $r0 = new Router();
    $r1 = new Router();

    $r0->use(
        "/a",
        $r1
    );

    $r1->use(
        "/b",
        fn() => 0
    );

    Sptf::expect(empty($r0->findPath("/a/b")))
        ->toBe(false);
});

Sptf::test("find path for deeply nested dynamic Routers", function () {
    $r2 = new Router();
    $r3 = new Router();

    $ret = [];

    $r2->use(
        Path::from("/@[user]")
            ->param("user", Ident::getInstance()),
        function () use (&$ret) {
            $ret[] = 0;
        },
        $r3
    );

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
    foreach ($r2->findPath("/@CaptSiro/post-420")[0]->endpoints as $endpoint) {
        $endpoint->call($request, new Response());
    }

    Sptf::expect($ret)
        ->toBe([0, 1, 2])
        ->compare(fn($a, $b) => Arrays::equal($a, $b));

    Sptf::expect(empty($r2->findPath("/@CaptSiro/post-420")))
        ->toBe(false);
});



Sptf::test("get correct url path for Resource", function () {
    $r0 = new Router();
    $r1 = new Router();

    $t0 = new TestResource();
    $t1 = new TestResource();

    $r0->use(
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

    Sptf::expect($t0->getUrl(Resource::URL_READ))
        ->toBe("/[unique]");

    Sptf::expect($t1->getUrl(Resource::URL_READ))
        ->toBe("/a/b/[unique]");
});

Sptf::test("handle any terminated paths", function () {
    $r0 = new Router();

    $r0->use(
        "/public/**",
        fn() => 0
    );

    Sptf::expect(empty($r0->findPath("/public/css/styles.css")))
        ->toBe(false);
});



/**
 * @param array<FoundNode> $path
 * @param $anyTerminatedCalled
 */
function shouldSetAnyTerminated(array $path, &$anyTerminatedCalled): void {
    Sptf::expect(count($path))
        ->toBe(2);

    $anyTerminatedCalled = false;
    foreach ($path[1]->endpoints as $endpoint) {
        $endpoint->call(Request::test(), new Response());
    }

    Sptf::expect($anyTerminatedCalled)
        ->toBe(true);
}

Sptf::test("any terminated paths are evaluated last", function () {
    $anyTerminatedCalled = false;
    $r0 = new Router();

    $r0->use(
        "/foo",
        fn() => 0
    );

    $r0->use(
        "/**",
        function () use (&$anyTerminatedCalled) {
            $anyTerminatedCalled = true;
        }
    );

    $r0->use(
        "/bar",
        fn() => 0
    );

    shouldSetAnyTerminated(
        $r0->findPath("/foo"),
        $anyTerminatedCalled
    );

    shouldSetAnyTerminated(
        $r0->findPath("/bar"),
        $anyTerminatedCalled
    );
});
<?php

use core\communication\Request;
use core\communication\Response;
use core\route\compiler\RouteCompiler;
use core\route\compiler\RouteCompilerConfig;
use core\route\compiler\RouteCompilerException;
use core\route\compiler\Token;
use core\route\compiler\Tokenizer;
use core\route\compiler\TokenType;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\route\Router;
use core\route\RouteSegment;
use core\route\RouteTree;
use core\route\Trace;
use core\url\Url;
use core\utils\Regex;
use sptf\Sptf;
use tests\utils\RouteChasm\actions\ActCounter;
use tests\utils\RouteChasm\actions\MatchRemainingPath;

Sptf::test("should tokenize routes correctly", function () {
    $routes = [
        "" => [
            Token::eof(),
        ],
        "/" => [
            new Token(TokenType::SLASH, "/"),
            Token::eof(),
        ],
        "foo" => [
            new Token(TokenType::IDENT, "foo"),
            Token::eof(),
        ],
        "/foo" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "foo"),
            Token::eof(),
        ],
        "/foo/bar" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "bar"),
            Token::eof(),
        ],
        "[foo]" => [
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),
            Token::eof(),
        ],
        "/[foo]" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),
            Token::eof(),
        ],
        "/@[foo]/[bar]/fizz" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "@"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),

            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "bar"),
            new Token(TokenType::BRACKET_R, "]"),

            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "fizz"),
            Token::eof(),
        ],
        "/@[foo]/*/bar" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "@"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),

            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::ANY, "*"),

            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "bar"),
            Token::eof(),
        ],
        "/@[foo]/**" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::IDENT, "@"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),

            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::ANY_TERMINATOR, "**"),
        ],
    ];

    foreach ($routes as $route => $expected) {
        $tokenized = [...(new Tokenizer($route))->tokenize()];

        foreach ($expected as $i => $token) {
            Sptf::expect($tokenized[$i])
                ->compare(
                    fn(Token $a, Token $b) => $a->getType() === $b->getType()
                        && $a->getLiteral() === $b->getLiteral()
                )
                ->toBe($token);
        }
    }
});

Sptf::test("should parse routes", function () {
    $any = ".+";
    $anyGroup = "$any";

    $foo = "foo+";
    $fooGroup = Regex::createNamedGroup("foo", $foo);

    $bar = "ba+r";
    $barGroup = Regex::createNamedGroup("bar", $bar);

    $routes = [
        ["", [], "/"],
        ["/", [], "/"],
        ["//", [], "/"],
        ["foo", [], "/foo"],
        ["/foo", [], "/foo"],
        ["/foo/bar", [], "/foo/bar"],
        ["/foo//bar", [], "/foo/bar"],
        ["/foo/////bar", [], "/foo/bar"],
        ["[foo]", ["foo" => $foo], "/$fooGroup"],
        ["/[foo]", ["foo" => $foo], "/$fooGroup"],
        ["/@[foo]/[bar]/fizz", ["foo" => $foo, "bar" => $bar], "/@$fooGroup/$barGroup/fizz"],
        ["/@[foo]/*/bar", ["foo" => $foo], "/@$fooGroup/$any/bar"],
        ["/@[foo]//*//bar", ["foo" => $foo], "/@$fooGroup/$any/bar"],
        ["/@[foo]/**", ["foo" => $foo], "/@$fooGroup/$any"],
        ["/@[foo]//**", ["foo" => $foo], "/@$fooGroup/$any"],
    ];

    $compiler = new RouteCompiler(
        (new RouteCompilerConfig())
            ->setAnyRegex($any)
            ->setMergeConsecutiveSlashes(true)
    );

    foreach ($routes as $tuple) {
        [$pattern, $parameters, $expected] = $tuple;
        $route = $compiler->parse($pattern, $parameters);
        Sptf::expect("$route")->toBe($expected);
    }
});

Sptf::test("should reconstruct source", function () {
    $routes = [
        "/",
        "/foo",
        "/foo/bar",
        "/[foo]",
        "/@[foo]/[bar]/fizz",
        "/@[foo]/*/bar",
        "/@[foo]/**",
    ];

    $compiler = new RouteCompiler(
        (new RouteCompilerConfig())
            ->setAnyRegex(".+")
            ->setMergeConsecutiveSlashes(true)
    );

    foreach ($routes as $pattern) {
        $route = $compiler->parse($pattern);

        $v = '/'. implode('/', array_map(fn(RouteSegment $x) => $x->getSource(), $route->getSegments()));
        Sptf::expect($pattern)->toBe($v);
    }
});

Sptf::test("refuse to parse consecutive slashes in route", function () {
    $routes = [
        "//",
        "foo//bar",
        "/foo////bar",
        "[foo]//[bar]",
        "/[foo]//[bar]",
        "/[foo]////[bar]",
    ];

    $parser = new RouteCompiler(
        (new RouteCompilerConfig())
            ->setMergeConsecutiveSlashes(false)
    );

    foreach ($routes as $route) {
        try {
            $parser->parse($route);
            Sptf::fail();
        } catch (RouteCompilerException $ignored) {
            Sptf::pass();
        }
    }
});

Sptf::test("fail parsing routes", function () {
    $routes = [
        "[a", "b]",
        "[a[b]]",
        "[foo/bar", "/[foo/bar",
        "]foo]", "/]foo]", "/foo/]bar]",
        "[/]", "/[/]", "/foo/[/]",
        "[1]", "/[1]", "/foo/[1]",
        "[#]", "/[#]", "/foo/[#]",
        "[ ]", "/[ ]", "/foo[ ]",
    ];

    $parser = new RouteCompiler();

    foreach ($routes as $route) {
        try {
            $parser->parse($route);
            Sptf::fail("Should have failed parsing path: '$route'");
        } catch (RouteCompilerException) {
            Sptf::pass();
        }
    }
});

Sptf::test("should return correct depth of route", function () {
    $routes = [
        "" => 0,
        "/" => 0,
        "foo" => 1,
        "/foo" => 1,
        "foo/bar" => 2,
        "/foo/bar" => 2,
        "/@[foo]/[bar]/fizz" => 3,
    ];

    $parser = new RouteCompiler();

    foreach ($routes as $route => $depth) {
        Sptf::expect($parser->parse($route)->getDepth())
            ->toBe($depth);
    }
});

Sptf::test("should create vertex", function () {
    $foo = "foo+";
    $fooSegment = Regex::create(Regex::createNamedGroup("foo", $foo));
    $barSegment = Regex::create("bar");

    $a = Route::from("/[foo]", ["foo" => $foo]);
    $b = Route::from("/[foo]/bar", ["foo" => $foo]);

    $tree = new RouteTree();
    $tree->getNode($a);
    $tree->getNode($b);

    $root = $tree->getRoot();

    Sptf::expect(count($root->getEdges()))->toBe(1);

    $fooEdge = $root->getEdges()[0];
    Sptf::expect($fooEdge->get()->getRegex())->toBe($fooSegment);
    Sptf::expect(count($fooEdge->getVertex()->getEdges()))->toBe(1);

    $barEdge = $fooEdge->getVertex()->getEdges()[0];
    Sptf::expect($barEdge->get()->getRegex())->toBe($barSegment);
});

/**
 * @param array<Trace<RouteNode, RouteSegment>> $traces
 * @param Request|null $request
 * @param Response|null $response
 * @return void
 */
function perform_actions(array $traces, ?Request $request = null, ?Response $response = null): void {
    $request ??= Request::test();
    $response ??= Response::test();

    foreach ($traces as $trace) {
        $index = 0;

        foreach ($trace->getVertexes() as $vertex) {
            $request->set(Request::PATH_INDEX, $index);

            foreach ($vertex->get()->getActions() as $action) {
                var_dump("performing ". $action->getActorName());
                $action->perform($request, $response);
            }

            $index++;
        }
    }
}

function assert_counts(array $counters, bool $reset = false): void {
    foreach ($counters as $tuple) {
        /** @var ActCounter $counter */
        [$counter, $count] = $tuple;
        Sptf::expect($counter->getN())->toBe($count);

        if ($counter->getN() !== $count) {
            var_dump($counter->getActorName() ." assertion failed (n != $count)");
        }

        if ($reset) {
            $counter->setN(0);
        }
    }
}

Sptf::test("should find correct vertexes", function () {
    $tree = new RouteTree();

    $root = new ActCounter("root");
    $tree
        ->getNode(Route::from("/"))
        ->addAction($root);

    $any = new ActCounter("any");
    $tree
        ->getNode(Route::from("/**"))
        ->addAction($any);

    $foo = new ActCounter("foo");
    $tree
        ->getNode(Route::from("/foo"))
        ->addAction($foo);

    $fooBar = new ActCounter("fooBar");
    $tree
        ->getNode(Route::from("/foo/bar"))
        ->addAction($fooBar);

    $dynamicFoo = new ActCounter("dynamicFoo");
    $tree
        ->getNode(Route::from("/[foo]", ["foo" => "fo+"]))
        ->addAction($dynamicFoo);

    $dynamicFooBar = new ActCounter("dynamicFooBar");
    $tree
        ->getNode(Route::from("/[foo]/[bar]", ["foo" => "fo+", "bar" => "ba?r"]))
        ->addAction($dynamicFooBar);

    perform_actions($tree->traceSearch(Path::from("/")));
    assert_counts([
        [$root, 1],
        [$any, 1],
        [$foo, 0],
        [$fooBar, 0],
        [$dynamicFoo, 0],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/non-existent")));
    assert_counts([
        [$root, 1],
        [$any, 1],
        [$foo, 0],
        [$fooBar, 0],
        [$dynamicFoo, 0],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foo")));
    assert_counts([
        [$root, 3],
        [$any, 3],
        [$foo, 1],
        [$fooBar, 0],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foo/bar")));
    assert_counts([
        [$root, 3],
        [$any, 3],
        [$foo, 1],
        [$fooBar, 1],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 1],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foooo")));
    assert_counts([
        [$root, 2],
        [$any, 2],
        [$foo, 0],
        [$fooBar, 0],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foooo/br")));
    assert_counts([
        [$root, 2],
        [$any, 2],
        [$foo, 0],
        [$fooBar, 0],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 1],
    ], true);
});

/**
 * @param array<Trace<RouteNode, RouteSegment>> $traces
 * @return void
 */
function perform_first_non_middleware_action(array $traces): void {
    $q = Request::test();
    $p = Response::test();

    Sptf::expect(count($traces) > 0)->toBe(true);
    if (count($traces) === 0) {
        return;
    }

    $trace = array_shift($traces);
    foreach ($trace->getVertexes() as $vertex) {
        foreach ($vertex->get()->getActions() as $action) {
            if (!$action->isMiddleware()) {
                $action->perform($q, $p);
            }
        }
    }
}

Sptf::test("should find RouteNodes in correct order", function () {
    $tree0 = new RouteTree();
    $tree1 = new RouteTree();

    $any = new ActCounter("any", isMiddleware: true);
    $foo = new ActCounter("foo", isMiddleware: false);

    $tree0
        ->getNode(Route::from("/**"))
        ->addAction($any);

    $tree0
        ->getNode(Route::from("/foo"))
        ->addAction($foo);

    $tree1
        ->getNode(Route::from("/foo"))
        ->addAction($foo);

    $tree1
        ->getNode(Route::from("/**"))
        ->addAction($any);

    perform_first_non_middleware_action($tree0->traceSearch(Path::from("/foo")));
    assert_counts([
        [$any, 0],
        [$foo, 1]
    ], true);

    perform_first_non_middleware_action($tree1->traceSearch(Path::from("/foo")));
    assert_counts([
        [$any, 0],
        [$foo, 1]
    ], true);
});

Sptf::test("should bind Action objects correctly", function () {
    $router = new Router();

    $foo = new ActCounter("foo");
    $bar = new ActCounter("bar");

    $router->use("/foo/bar", $foo, $bar);

    perform_actions($router->find(Path::from("/foo/bar")));
    assert_counts([
        [$foo, 1],
        [$bar, 1]
    ]);
});

Sptf::test("should bind Router correctly", function () {
    $any = new ActCounter("any");
    $foo = new ActCounter("foo");
    $bar = new ActCounter("bar");

    $router1 = new Router();
    $router1
        ->use("/**", $any)
        ->use("/foo", $foo);

    $router0 = new Router();
    $router0
        ->use("/bar", $bar)
        ->bind("/bar", $router1);

    perform_actions($router0->find(Path::from("/bar/foo")));
    assert_counts([
        [$bar, 1],
        [$any, 1],
        [$foo, 1],
    ], true);

    perform_actions($router0->find(Path::from("/bar")));
    assert_counts([
        [$bar, 1],
        [$any, 1],
        [$foo, 0],
    ], true);

    Sptf::expect($router0->getRoute()->toPath()->toString())->toBe("/");
    Sptf::expect($router1->getRoute()->toPath()->toString())->toBe("/bar");
});

Sptf::test("should return expected remaining paths", function () {
    $url = Url::from("http://localhost/request/path/to/file.txt");

    $request = Request::test(url: $url);
    $path = Path::from($request->getUrl()->getPath()->toString());

    $tests = [
        "/**" => "/request/path/to/file.txt",
        "/request/**" => "/path/to/file.txt",
        "/request/path/**" => "/to/file.txt",
        "/request/path/to/**" => "/file.txt",
    ];

    $actions = [];
    $router = new Router();

    foreach ($tests as $route => $expected) {
        $router->use($route, $actions[] = new MatchRemainingPath($expected));
    }

    perform_actions($router->find($path), $request);

    foreach ($actions as $action) {
        Sptf::expect($action->isPerformed())->toBe(true);
    }
});

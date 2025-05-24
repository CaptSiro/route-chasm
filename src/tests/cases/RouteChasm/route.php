<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use core\actions\ActCounter;
use core\actions\Action;
use core\communication\Request;
use core\communication\Response;
use core\route\compiler\RouteCompiler;
use core\route\compiler\RouteCompilerException;
use core\route\compiler\RouteCompilerOptions;
use core\route\compiler\Token;
use core\route\compiler\Tokenizer;
use core\route\compiler\TokenType;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\route\RouteTree;
use core\route\RouteSegment;
use core\route\Trace;
use core\utils\Regex;
use sptf\Sptf;

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

    Sptf::allowPrinting();

    foreach ($routes as $route => $expected) {
        $tokenized = [...(new Tokenizer($route))->tokenize()];

        foreach ($expected as $i => $token) {
            Sptf::expect($tokenized[$i])
                ->compare(fn(Token $a, Token $b) => $a->type === $b->type && $a->literal === $b->literal)
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

    $parser = new RouteCompiler(
        (new RouteCompilerOptions())
            ->setAnyRegex($any)
            ->setMergeConsecutiveSlashes(true)
    );

    foreach ($routes as $tuple) {
        [$pattern, $parameters, $expected] = $tuple;
        $route = $parser->parse($pattern, $parameters);
        Sptf::expect("$route")->toBe($expected);
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
        (new RouteCompilerOptions())
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
    Sptf::allowPrinting();

    $foo = "foo+";
    $fooSegment = Regex::create(Regex::createNamedGroup("foo", $foo));
    $barSegment = Regex::create("bar");

    $a = Route::from("/[foo]", ["foo" => $foo]);
    $b = Route::from("/[foo]/bar", ["foo" => $foo]);

    $tree = new RouteTree();
    $tree->getTerminalVertex($a);
    $tree->getTerminalVertex($b);

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
 * @return void
 */
function perform_actions(array $traces): void {
    $q = Request::test();
    $p = Response::test();

    foreach ($traces as $trace) {
        foreach ($trace->getVertexes() as $vertex) {
            foreach ($vertex->get()->getActions() as $action) {
                $action->perform($q, $p);
            }
        }
    }
}

function assert_counts(array $counters, bool $reset = false): void {
    foreach ($counters as $tuple) {
        /** @var ActCounter $counter */
        [$counter, $count] = $tuple;
        Sptf::expect($counter->getN())->toBe($count);

        if ($reset) {
            $counter->setN(0);
        }
    }
}

Sptf::test("should find correct vertexes", function () {
    $tree = new RouteTree();

    $root = new ActCounter("root");
    $tree
        ->getTerminalVertex(Route::from("/"))
        ->addAction($root);

    $any = new ActCounter("any");
    $tree
        ->getTerminalVertex(Route::from("/**"))
        ->addAction($any);

    $foo = new ActCounter("foo");
    $tree
        ->getTerminalVertex(Route::from("/foo"))
        ->addAction($foo);

    $fooBar = new ActCounter("fooBar");
    $tree
        ->getTerminalVertex(Route::from("/foo/bar"))
        ->addAction($fooBar);

    $dynamicFoo = new ActCounter("dynamicFoo");
    $tree
        ->getTerminalVertex(Route::from("/[foo]", ["foo" => "fo+"]))
        ->addAction($dynamicFoo);

    $dynamicFooBar = new ActCounter("dynamicFooBar");
    $tree
        ->getTerminalVertex(Route::from("/[foo]/[bar]", ["foo" => "fo+", "bar" => "ba?r"]))
        ->addAction($dynamicFooBar);

    perform_actions($tree->traceSearch(Path::from("/")));
    assert_counts([
        [$root, 1],
        [$any, 0],
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
        [$any, 1],
        [$foo, 1],
        [$fooBar, 0],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foo/bar")));
    assert_counts([
        [$root, 3],
        [$any, 1],
        [$foo, 1],
        [$fooBar, 1],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 1],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foooo")));
    assert_counts([
        [$root, 2],
        [$any, 1],
        [$foo, 0],
        [$fooBar, 0],
        [$dynamicFoo, 1],
        [$dynamicFooBar, 0],
    ], true);

    perform_actions($tree->traceSearch(Path::from("/foooo/br")));
    assert_counts([
        [$root, 2],
        [$any, 1],
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
function perform_first_action(array $traces): void {
    $q = Request::test();
    $p = Response::test();

    Sptf::expect(count($traces) > 0)->toBe(true);
    if (count($traces) === 0) {
        return;
    }

    $trace = array_shift($traces);
    foreach ($trace->getVertexes() as $vertex) {
        foreach ($vertex->get()->getActions() as $action) {
            $action->perform($q, $p);
        }
    }
}

Sptf::test("should find RouteNodes in correct order", function () {
    Sptf::allowPrinting();

    $tree0 = new RouteTree();
    $tree1 = new RouteTree();

    $any = new ActCounter("any");
    $foo = new ActCounter("foo");

    $tree0
        ->getTerminalVertex(Route::from("/**"))
        ->addAction($any);

    $tree0
        ->getTerminalVertex(Route::from("/foo"))
        ->addAction($foo);

    $tree1
        ->getTerminalVertex(Route::from("/foo"))
        ->addAction($foo);

    $tree1
        ->getTerminalVertex(Route::from("/**"))
        ->addAction($any);

    perform_first_action($tree0->traceSearch(Path::from("/foo")));
    assert_counts([
        [$any, 0],
        [$foo, 1]
    ], true);

    perform_first_action($tree1->traceSearch(Path::from("/foo")));
    assert_counts([
        [$any, 0],
        [$foo, 1]
    ], true);
});

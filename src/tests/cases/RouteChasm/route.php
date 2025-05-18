<?php

use core\route\parser\RouteParser;
use core\route\parser\RouteParsingException;
use core\route\parser\Token;
use core\route\parser\Tokenizer;
use core\route\parser\TokenType;
use core\route\Route;
use core\route\RouteTree;
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
            Token::eof()
        ],
        "/[foo]" => [
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::BRACKET_L, "["),
            new Token(TokenType::IDENT, "foo"),
            new Token(TokenType::BRACKET_R, "]"),
            Token::eof()
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
    $fooGroup = RouteParser::createParameter("foo", $foo);

    $bar = "ba+r";
    $barGroup = RouteParser::createParameter("bar", $bar);

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

    $parser = new RouteParser(
        anyRegex: $any,
        mergeConsecutiveSlashes: true
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

    $parser = new RouteParser(mergeConsecutiveSlashes: false);

    foreach ($routes as $route) {
        try {
            $parser->parse($route);
            Sptf::fail();
        } catch (RouteParsingException $ignored) {
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

    $parser = new RouteParser();

    foreach ($routes as $route) {
        try {
            $parser->parse($route);
            Sptf::fail("Should have failed parsing path: '$route'");
        } catch (RouteParsingException) {
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

    $parser = new RouteParser();

    foreach ($routes as $route => $depth) {
        Sptf::expect($parser->parse($route)->getDepth())
            ->toBe($depth);
    }
});

Sptf::test("should create vertex", function () {
    $foo = "foo+";
    $fooSegment = Route::createSegmentRegex(RouteParser::createParameter("foo", $foo));
    $barSegment = Route::createSegmentRegex("bar");

    $a = Route::from("/[foo]", ["foo" => $foo]);
    $b = Route::from("/[foo]/bar", ["foo" => $foo]);

    $tree = new RouteTree();
    $tree->getVertex($a);
    $tree->getVertex($b);

    $root = $tree->getRoot();

    Sptf::expect(count($root->getEdges()))->toBe(1);

    $fooEdge = $root->getEdges()[0];
    Sptf::expect($fooEdge->getValue())->toBe($fooSegment);
    Sptf::expect(count($fooEdge->getVertex()->getEdges()))->toBe(1);

    $barEdge = $fooEdge->getVertex()->getEdges()[0];
    Sptf::expect($barEdge->getValue())->toBe($barSegment);
});

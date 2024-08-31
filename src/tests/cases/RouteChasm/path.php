<?php

use core\path\parser\Parser;
use core\path\parser\Token;
use core\path\parser\Tokenizer;
use core\path\parser\TokenType;
use core\path\PartType;
use core\path\Path;
use core\utils\Strings;
use patterns\Charset;
use patterns\Ident;
use patterns\Number;
use sptf\Sptf;



Sptf::test("Path::fromLiteral must match \$path->__toString output", function () {
    $arg = "card/[id]-[name]";
    Sptf::expect((string) Path::from($arg))->toBe($arg);
});



Sptf::test("tokenize path /u/id-[id]", function () {
    $path = "/u/id-[id]";
    /** @var Token[] $found */
    $found = [...(new Tokenizer($path))->tokenize()];

    /** @var Token[] $final */
    $final = [
        new Token(TokenType::SLASH, "/"),
        new Token(TokenType::IDENT, "u"),
        new Token(TokenType::SLASH, "/"),
        new Token(TokenType::IDENT, "id-"),
        new Token(TokenType::BRACKET_L, "["),
        new Token(TokenType::IDENT, "id"),
        new Token(TokenType::BRACKET_R, "]"),
        new Token(TokenType::EOF, "\0"),
    ];
    $count = count($final);

    Sptf::expect(count($found))->toBe($count);

    for ($i = 0; $i < count($final); $i++) {
        Sptf::expect($found[$i]->type)->toBe($final[$i]->type);
        Sptf::expect($found[$i]->literal)->toBe($final[$i]->literal);
    }
});



Sptf::test("parse self-referencing paths", function () {
    $paths = ["", "/"];

    $final = Path::fromRaw([]);
    $compare = fn(Path $a, Path $b) => Path::compare($a, $b);

    foreach ($paths as $path) {
        try {
            $p = Parser::parse($path);

            Sptf::expect($p)->toBe($final)->compare($compare);
        } catch (Exception) {
            Sptf::fail("Should have parsed path: '$path'");
            continue;
        }
    }
});



Sptf::test("parse paths", function () {
    $path = "/u/id-[id][user_name]/";
    $final = Path::fromRaw([
        [
            [PartType::STATIC, "u"]
        ],
        [
            [PartType::STATIC, "id-"],
            [PartType::DYNAMIC, "id"],
            [PartType::DYNAMIC, "user_name"],
        ]
    ]);

    try {
        $p = Parser::parse($path);
    } catch (Exception $e) {
        Sptf::fail("Path is invalid: ". $e->getMessage());
        return;
    }

    Sptf::expect($p)->toBe($final)
        ->compare(fn(Path $a, Path $b) => Path::compare($a, $b));
});



Sptf::test("fail parsing", function () {
    $paths = ["/u//", "//", "///", "[a", "b]", "[a[b]]", "[/]"];

    foreach ($paths as $path) {
        try {
            Parser::parse($path);
        } catch (Exception) {
            Sptf::pass();
            continue;
        }

        Sptf::fail("Should have failed parsing path: '$path'");
    }
});



Sptf::test("bind parameters", function () {
    $pass = true;
    $params = [
        "a" => Number::getInstance(),
        "b" => (new Charset())->addRange('a', 'c'),
        "c" => Number::getInstance(),
    ];

    try {
        $path = Parser::parse("/[a]/b-[b]/c-[c]-c");

        foreach ($params as $name => $value) {
            $path->param($name, $value);
        }

        foreach ($path->getSegments() as $segment) {
            foreach ($segment->getParts() as $part) {
                foreach ($params as $name => $value) {
                    if ($part->type === PartType::DYNAMIC
                        && $part->literal === $name
                        && $part->pattern !== $value) {
                        Sptf::fail("Parameter of name '$name' must have pattern $value");
                        $pass = false;
                    }
                }
            }
        }
    } catch (Exception $e) {
        Sptf::fail($e->getMessage());
        $pass = false;
    }

    if ($pass) {
        Sptf::pass();
    }
});



function segmentShouldMatch(Path $path, array $matches, bool $toBe): void {
    $segment = $path->getSegments()[0];

    foreach ($matches as $match) {
        $expectation = $segment->test($match, $m);
        Sptf::expect($expectation)
            ->toBe($toBe);

        if ($expectation !== $toBe) {
            var_dump($match);
        }
    }
}

Sptf::test("matching compound parts of segment", function () {
    segmentShouldMatch(
        Path::from("[id]-[name]")
            ->param("id", Number::getInstance())
            ->param("name", Ident::getInstance()),
        [
            "0-a",
            "123-a",
            "1-foo",
            "123-foo",
            "123-foo-bar",
        ],
        true
    );

    segmentShouldMatch(
        Path::from("[id]-[name]")
            ->param("id", Number::getInstance())
            ->param("name", Ident::getInstance()),
        array_merge(
            str_split(Strings::CHARS_SPECIALS() . Strings::CHARS_ALPHA() . strtoupper(Strings::CHARS_ALPHA())),
            array_map(fn($x) => "0-$x", str_split(Strings::CHARS_SPECIALS() . Strings::CHARS_NUMBERS())),
            [
                "0-foo bar",
                "0-foo&bar",
            ]
        ),
        false
    );
});

Sptf::test("should handle any token by it self", function () {
    segmentShouldMatch(
        Path::from("*"),
        array_merge(
            str_split(Strings::CHARS_NUMBERS() . Strings::CHARS_ALPHA() . Strings::CHARS_ALPHA_UPPER() . Strings::CHARS_SPECIALS()),
            [
                "a",
                "0-a",
                "123-a",
                "1-foo",
                "123-foo",
                "123-foo_BAR",
            ]
        ),
        true
    );
});

Sptf::test("should handle any token prepended with static token", function () {
    segmentShouldMatch(
        Path::from("foo-*"),
        [
            "foo-a",
            "foo-0-a",
            "foo-123-a",
            "foo-1-foo",
            "foo-123-foo",
            "foo-123-foo_BAR",
        ],
        true
    );

    segmentShouldMatch(
        Path::from("foo-*"),
        [
            "a",
            "0-a",
            "123-a",
            "1-foo",
            "123-foo",
            "123-foo_BAR",
        ],
        false
    );
});

Sptf::test("should handle any token prepended with dynamic token", function () {
    segmentShouldMatch(
        Path::from("[id]-*")
            ->param("id", Number::getInstance()),
        array_merge(
            array_map(fn($x) => "$x-any", str_split(Strings::CHARS_NUMBERS())),
            [
                "0-a",
                "123-a",
                "123-foo",
                "123-foo_BAR",
                "203-0-a",
                "203-123-a",
                "203-1-foo",
                "203-123-foo",
                "203-123-foo_BAR",
            ]
        ),
        true
    );

    segmentShouldMatch(
        Path::from("[id]-*")
            ->param("id", Number::getInstance()),
        str_split(Strings::CHARS_SPECIALS() . Strings::CHARS_ALPHA() . Strings::CHARS_ALPHA_UPPER()),
        false
    );
});
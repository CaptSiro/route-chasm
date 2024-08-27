<?php

use core\utils\Strings;
use patterns\AnyString;
use patterns\Base64;
use patterns\Charset;
use patterns\Exact;
use patterns\Ident;
use patterns\Number;
use patterns\Pattern;
use patterns\Stream;
use sptf\Sptf;



Sptf::test("correct order of Stream elements", function () {
    $resource = "fizzbuzz";
    $stream = new Stream($resource);

    for ($i = 0; $i < strlen($resource) && !$stream->isExhausted(); $i++) {
        Sptf::expect($stream->current())->toBe($resource[$i]);
        $stream->next();
    }

    Sptf::expect($stream->isExhausted())->toBe(true);
});



function charsetShouldContain(Charset $charset, array $characters, bool $toBe): void {
    foreach ($characters as $char) {
        Sptf::expect($charset->contains($char))
            ->toBe($toBe);

        if ($charset->contains($char) !== $toBe) {
            var_dump($char);
        }
    }
}

Sptf::test("Charset should contain correct characters", function () {
    charsetShouldContain(
        (new Charset())
            ->addRange('0', '9'),
        str_split(Strings::CHARS_NUMBERS()),
        true
    );
    charsetShouldContain(
        (new Charset())
            ->addRange('0', '9'),
        str_split(Strings::CHARS_ALPHA() . Strings::CHARS_ALPHA_UPPER() . Strings::CHARS_SPECIALS()),
        false
    );

    charsetShouldContain(
        (new Charset())
            ->addRange('a', 'z'),
        str_split(Strings::CHARS_ALPHA()),
        true
    );
    charsetShouldContain(
        (new Charset())
            ->addRange('a', 'z'),
        str_split(Strings::CHARS_NUMBERS() . Strings::CHARS_ALPHA_UPPER() . Strings::CHARS_SPECIALS()),
        false
    );

    charsetShouldContain(
        (new Charset())
            ->addRange('A', 'Z'),
        str_split(Strings::CHARS_ALPHA_UPPER()),
        true
    );
    charsetShouldContain(
        (new Charset())
            ->addRange('A', 'Z'),
        str_split(Strings::CHARS_ALPHA() . Strings::CHARS_NUMBERS() . Strings::CHARS_SPECIALS()),
        false
    );

    $specials = new Charset();
    foreach (str_split(Strings::CHARS_SPECIALS()) as $char) {
        $specials->add($char);
    }

    charsetShouldContain(
        $specials,
        str_split(Strings::CHARS_SPECIALS()),
        true
    );
    charsetShouldContain(
        $specials,
        str_split(Strings::CHARS_ALPHA() . Strings::CHARS_ALPHA_UPPER() . Strings::CHARS_NUMBERS()),
        false
    );
});



function patternShouldMatch(Pattern $pattern, array $matches, bool $toBe, bool $doPipeline = false): void {
    foreach ($matches as $match) {
        $expect = $pattern->match($match);
        Sptf::expect($expect)
            ->toBe($toBe);

        if ($expect !== $toBe) {
            var_dump($match);
        }

        if (!$doPipeline) {
            continue;
        }

        $m = "";
        $stream = new Stream($match);
        $expectation = $pattern->matchPipeline($stream, $m);

        $passed = $expectation && $stream->isExhausted();
        if ($toBe === false) {
            $passed = !$passed;
        }

        Sptf::expect($passed)
            ->toBe(true);

        if (!$passed) {
            var_dump($match);
        }
    }
}



Sptf::test("AnyString matches anything", function () {
    patternShouldMatch(new AnyString(), [
        "",
        "_",
        "1",
        "a",
        "foo",
        "BAR",
        "@user",
        "#comment",
        "+ěščřžýááé"
    ], true, true);

    $lengthRestricted = (new AnyString())
        ->setMinLength(4);

    patternShouldMatch(
        $lengthRestricted,
        [
            "fizz",
            "buzz",
            "fizzbuzz_1234&*"
        ],
        true, true
    );

    patternShouldMatch(
        $lengthRestricted,
        [
            "",
            "1",
            "22",
            "333",
            "foo",
            "bar",
        ],
        false, true
    );
});



Sptf::test("Base64 matches url safe version of base64", function () {
    patternShouldMatch(new Base64(), [
        Strings::CHARS_ALPHA(),
        Strings::CHARS_ALPHA_UPPER(),
        Strings::CHARS_NUMBERS(),
        "_-"
    ], true, true);

    patternShouldMatch(new Base64(), array_merge(
        str_split(str_replace('-_', '', Strings::CHARS_SPECIALS())),
        ['ěščřžýáíé']
    ), false, true);
});



Sptf::test("Exact should match only exact strings", function () {
    patternShouldMatch(new Exact(""), [""], true, true);
    patternShouldMatch(new Exact("foo"), ["foo"], true, true);
    patternShouldMatch(new Exact("foo"), ["bar"], false, true);
    patternShouldMatch(new Exact(Strings::CHARS_NUMBERS()), [Strings::CHARS_NUMBERS()], true, true);
    patternShouldMatch(new Exact(Strings::CHARS_ALPHA()), [Strings::CHARS_ALPHA()], true, true);
    patternShouldMatch(new Exact(Strings::CHARS_ALPHA()), [Strings::CHARS_ALPHA()], true, true);
    patternShouldMatch(new Exact(Strings::CHARS_SPECIALS()), [Strings::CHARS_SPECIALS()], true, true);
});



Sptf::test("Ident should match identifiers", function () {
    patternShouldMatch(new Ident(), [
        "foo",
        "Foo",
        "FOO",
        "foo1",
        "foo-2",
        "Foo-3",
        "FOO-4",
        "FOO-69-bar",
    ], true, true);

    patternShouldMatch(new Ident(), array_merge(
        str_split(Strings::CHARS_SPECIALS()),
        str_split(Strings::CHARS_NUMBERS()),
        [
            '5-pillows',
            'foo bar',
            ' foo',
            '-bar',
        ]
    ), false, true);
});



Sptf::test("Number should match only whole numbers without formatting", function () {
    patternShouldMatch(new Number(), array_merge(
        str_split(Strings::CHARS_NUMBERS()),
        [
            '123',
            '10000000',
            '911',
            '727',
        ]
    ), true, true);

    patternShouldMatch(new Number(), array_merge(
        str_split(Strings::CHARS_ALPHA()),
        str_split(Strings::CHARS_SPECIALS()),
        [
            '10 000 000',
            '9.11',
            '7,27',
        ]
    ), false);
});

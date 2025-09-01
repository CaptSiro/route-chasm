<?php

use core\communication\Request;
use core\http\HttpHeader;
use core\locale\selectors\AcceptLanguageSelector;
use sptf\Sptf;

function r(string $value): Request {
    $request = Request::test();
    $request->setHeader(HttpHeader::ACCEPT_LANGUAGE, $value);
    return $request;
}

Sptf::test("parse simple language tags", function () {
    $parser = new AcceptLanguageSelector();

    Sptf::expect($parser->select(r("en")))->toBe("en");
    Sptf::expect($parser->select(r("cs-CZ")))->toBe("cs-CZ");
    Sptf::expect($parser->select(r("en-US,en;q=0.9")))->toBe("en-US");
});

Sptf::test("handle q-values correctly", function () {
    $parser = new AcceptLanguageSelector();

    Sptf::expect($parser->select(r("en;q=0.8, cs;q=0.9")))->toBe("cs");
    Sptf::expect($parser->select(r("fr;q=0.2, de;q=0.5, en;q=0.7")))->toBe("en");
});

Sptf::test("prefer more specific tags", function () {
    $parser = new AcceptLanguageSelector();

    Sptf::expect($parser->select(r("en-US,en;q=1.0")))->toBe("en-US");
    Sptf::expect($parser->select(r("zh-Hant, zh;q=1.0")))->toBe("zh-Hant");
});

Sptf::test("wildcard fallback", function () {
    $parser = new AcceptLanguageSelector();

    Sptf::expect($parser->select(r("*")))->toBe(null);
    Sptf::expect($parser->select(r("*, en;q=0.8")))->toBe("en");
});

Sptf::test("skip invalid entries", function () {
    $parser = new AcceptLanguageSelector();

    Sptf::expect($parser->select(r("foo@bar, en;q=0.9")))->toBe("en");
    Sptf::expect($parser->select(r("")))->toBe(null);
});

Sptf::test("respect order when all else equal", function () {
    $parser = new AcceptLanguageSelector();

    // Both have q=1 and specificity=1 → first wins
    Sptf::expect($parser->select(r("de, fr")))->toBe("de");
});
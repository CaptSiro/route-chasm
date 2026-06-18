<?php

use core\communication\Request;
use core\http\HttpHeader;
use core\locale\selectors\AcceptLanguageSelector;
use core\tf\Test;
use core\tf\Unit;

function r(string $value): Request {
    $request = Request::test();
    $request->setHeader(HttpHeader::ACCEPT_LANGUAGE, $value);
    return $request;
}

Test::case("parse simple language tags", function () {
    $parser = new AcceptLanguageSelector();

    Unit::expect($parser->select(r("en")))->toBe("en");
    Unit::expect($parser->select(r("cs-CZ")))->toBe("cs-CZ");
    Unit::expect($parser->select(r("en-US,en;q=0.9")))->toBe("en-US");
});

Test::case("handle q-values correctly", function () {
    $parser = new AcceptLanguageSelector();

    Unit::expect($parser->select(r("en;q=0.8, cs;q=0.9")))->toBe("cs");
    Unit::expect($parser->select(r("fr;q=0.2, de;q=0.5, en;q=0.7")))->toBe("en");
});

Test::case("prefer more specific tags", function () {
    $parser = new AcceptLanguageSelector();

    Unit::expect($parser->select(r("en-US,en;q=1.0")))->toBe("en-US");
    Unit::expect($parser->select(r("zh-Hant, zh;q=1.0")))->toBe("zh-Hant");
});

Test::case("wildcard fallback", function () {
    $parser = new AcceptLanguageSelector();

    Unit::expect($parser->select(r("*")))->toBe(null);
    Unit::expect($parser->select(r("*, en;q=0.8")))->toBe("en");
});

Test::case("skip invalid entries", function () {
    $parser = new AcceptLanguageSelector();

    Unit::expect($parser->select(r("foo@bar, en;q=0.9")))->toBe("en");
    Unit::expect($parser->select(r("")))->toBe(null);
});

Test::case("respect order when all else equal", function () {
    $parser = new AcceptLanguageSelector();

    // Both have q=1 and specificity=1 → first wins
    Unit::expect($parser->select(r("de, fr")))->toBe("de");
});
<?php

use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\url\Url;
use core\utils\Arrays;
use sptf\Sptf;



Sptf::test('creates URL from server vars', function () {
    Sptf::allowPrinting();

    $server_reset = Arrays::set($_SERVER, [
        "REQUEST_URI" => "http://subdomain.localhost.com/route-chasm/foo/bar/fizz?q=1234&buzz",
        "REQUEST_SCHEME" => "http",
        "HTTP_HOST" => "subdomain.localhost.com",
        "QUERY_STRING" => "q=1234&buzz"
    ]);

    $get_reset = Arrays::set($_GET, [
        "q" => "1234",
        "buzz" => ""
    ]);

    $url = Url::fromRequest();

    Sptf::expect($url->getHost())->toBe("subdomain.localhost.com");
    Sptf::expect($url->getPath()->toString())->toBe("/route-chasm/foo/bar/fizz");
    Sptf::expect($url->getQuery())
        ->toBe(new StrictMap([
            "q" => "1234",
            "buzz" => ""
        ]))
        ->compare(fn(StrictDictionary $a, StrictDictionary $b) => Arrays::equal($a->toArray(), $b->toArray()));
    Sptf::expect($url->getProtocol())->toBe("http");

    Sptf::expect($url->getQuery()->get("q"))->toBe("1234");
    Sptf::expect($url->getQuery()->get("buzz"))->toBe("");

    $server_reset();
    $get_reset();
});



Sptf::test('parse fully qualified URL', function () {
    Sptf::allowPrinting();

    $url = Url::from('http://localhost/route-chasm/fizz/buzz?ping=pong&foo=bar&fizz');

    Sptf::expect($url->getProtocol())
        ->toBe('http');

    Sptf::expect($url->getHost())
        ->toBe('localhost');

    Sptf::expect($url->getPath()->toString())
        ->toBe('/route-chasm/fizz/buzz');

    Sptf::expect($url->getQuery())
        ->toBe(new StrictMap(["ping" => "pong", "foo" => "bar", "fizz" => ""]))
        ->compare(fn(StrictDictionary $a, StrictDictionary $b) => Arrays::equal($a->toArray(), $b->toArray()));
});

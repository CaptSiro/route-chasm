<?php

use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\tf\Test;
use core\tf\Unit;
use core\url\Url;
use core\utils\Arrays;



Test::case('creates URL from server vars', function () {
    Test::allowPrinting();

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

    Unit::expect($url->getHost())->toBe("subdomain.localhost.com");
    Unit::expect($url->getPath()->toString())->toBe("/route-chasm/foo/bar/fizz");
    Unit::expect($url->getQuery())
        ->toBe(new StrictMap([
            "q" => "1234",
            "buzz" => ""
        ]))
        ->compare(fn(StrictDictionary $a, StrictDictionary $b) => Arrays::equal($a->toArray(), $b->toArray()));
    Unit::expect($url->getProtocol())->toBe("http");

    Unit::expect($url->getQuery()->get("q"))->toBe("1234");
    Unit::expect($url->getQuery()->get("buzz"))->toBe("");

    $server_reset();
    $get_reset();
});



Test::case('parse fully qualified URL', function () {
    Test::allowPrinting();

    $url = Url::from('http://localhost/route-chasm/fizz/buzz?ping=pong&foo=bar&fizz');

    Unit::expect($url->getProtocol())
        ->toBe('http');

    Unit::expect($url->getHost())
        ->toBe('localhost');

    Unit::expect($url->getPath()->toString())
        ->toBe('/route-chasm/fizz/buzz');

    Unit::expect($url->getQuery())
        ->toBe(new StrictMap(["ping" => "pong", "foo" => "bar", "fizz" => ""]))
        ->compare(fn(StrictDictionary $a, StrictDictionary $b) => Arrays::equal($a->toArray(), $b->toArray()));
});

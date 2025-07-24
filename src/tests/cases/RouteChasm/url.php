<?php

use core\App;
use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\url\Url;
use core\url\UrlV2;
use core\utils\Arrays;
use sptf\Sptf;



Sptf::test('creates URL from server vars', function () {
    $server_reset = Arrays::set($_SERVER, [
        "REQUEST_URI" => "http://poggy.localhost.com/RoutePass/abc/lmao/kek?q=1234&mnoice=69420",
        "REQUEST_SCHEME" => "http",
        "HTTP_HOST" => "poggy.localhost.com",
        "QUERY_STRING" => "q=1234&nice=69420"
    ]);

    $get_reset = Arrays::set($_GET, [
        "q" => "1234",
        "nice" => "69420"
    ]);

    App::getInstance()->getOptions()->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, false);

    $url = Url::fromRequest();

    Sptf::expect($url->getHost())->toBe("poggy.localhost.com");
    Sptf::expect($url->getPath())->toBe("/RoutePass/abc/lmao/kek");
    Sptf::expect($url->getQueryString())->toBe("q=1234&nice=69420");
    Sptf::expect($url->getProtocol())->toBe("http");

    Sptf::expect($url->getQuery()->get("q"))->toBe("1234");
    Sptf::expect($url->getQuery()->get("nice"))->toBe("69420");

    $server_reset();
    $get_reset();
    App::getInstance()->getOptions()->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
});



Sptf::test('parse fully qualified URL', function () {
    Sptf::allowPrinting();
    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, false);

    $url = Url::from('http://localhost/nocoma?ping=pong&foo=bar&fizz');

    Sptf::expect($url->getProtocol())
        ->toBe('http');

    Sptf::expect($url->getHost())
        ->toBe('localhost');

    Sptf::expect($url->getPath())
        ->toBe('/nocoma');

    Sptf::expect($url->getQueryString())
        ->toBe('ping=pong&foo=bar&fizz');

    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
});



Sptf::test('[UrlV2]: creates URL from server vars', function () {
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

    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, false);

    $url = UrlV2::fromRequest();

    Sptf::expect($url->getDomain())->toBe("subdomain.localhost.com");
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

    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
});



Sptf::test('[UrlV2]: parse fully qualified URL', function () {
    Sptf::allowPrinting();

    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, false);

    $url = UrlV2::from('http://localhost/route-chasm/fizz/buzz?ping=pong&foo=bar&fizz');

    Sptf::expect($url->getProtocol())
        ->toBe('http');

    Sptf::expect($url->getDomain())
        ->toBe('localhost');

    Sptf::expect($url->getPath()->toString())
        ->toBe('/route-chasm/fizz/buzz');

    Sptf::expect($url->getQuery())
        ->toBe(new StrictMap(["ping" => "pong", "foo" => "bar", "fizz" => ""]))
        ->compare(fn(StrictDictionary $a, StrictDictionary $b) => Arrays::equal($a->toArray(), $b->toArray()));

    App::getInstance()
        ->getOptions()
        ->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
});

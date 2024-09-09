<?php

use core\App;
use core\url\Url;
use sptf\Sptf;

require_once __DIR__ ."/../../utils/RouteChasm/set-array.php";



Sptf::test('creates URL from server vars', function () {
    $server_reset = set_array($_SERVER, [
        "REQUEST_URI" => "http://poggy.localhost.com/RoutePass/abc/lmao/kek?q=1234&mnoice=69420",
        "REQUEST_SCHEME" => "http",
        "HTTP_HOST" => "poggy.localhost.com",
        "QUERY_STRING" => "q=1234&nice=69420"
    ]);

    $get_reset = set_array($_GET, [
        "q" => "1234",
        "nice" => "69420"
    ]);

    App::getInstance()->options->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, false);

    $url = Url::fromRequest();

    Sptf::expect($url->getHost())->toBe("poggy.localhost.com");
    Sptf::expect($url->getPath())->toBe("/RoutePass/abc/lmao/kek");
    Sptf::expect($url->getQueryString())->toBe("q=1234&nice=69420");
    Sptf::expect($url->getProtocol())->toBe("http");

    Sptf::expect($url->query->get("q"))->toBe("1234");
    Sptf::expect($url->query->get("nice"))->toBe("69420");

    $server_reset();
    $get_reset();
    App::getInstance()->options->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
});

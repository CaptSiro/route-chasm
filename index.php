<?php

use components\core\HttpError\HttpError;
use core\App;
use core\database\config\BasicConfig;
use core\database\Database;
use core\http\Http;
use core\http\HttpCode;
use core\Request;
use core\Response;
use sptf\Sptf;

require_once __DIR__ ."/src/autoload.php";



Database::configure(new BasicConfig(
    "localhost",
    "flashcards",
    "root",
    ""
));

$app = App::getInstance();
$app->options->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);

$router = $app->getMainRouter();



$router->expose("/public", (new \core\endpoints\Directory(__DIR__ . "/public"))
    ->setFlag(\core\endpoints\Directory::FLAG_LIST_DIRECTORIES));

$router->use(
    "/error",
    new HttpError("I'm a teapot", HttpCode::CE_IM_A_TEAPOT)
);

$router->use(
    "/",
    Http::get(
        fn() => Sptf::testDirectory(__DIR__ . "/src/tests/cases"),
        fn(Request $request, Response $response) => $response->flush()
    )->query("_test")
);



$app->serve();
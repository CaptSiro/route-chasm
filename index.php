<?php

use components\core\HttpError;
use core\App;
use core\Http;
use core\Response;
use sptf\Sptf;

require_once __DIR__ ."/src/autoload.php";



$app = App::getInstance();
$app->options->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);

$router = $app->getMainRouter();



$router->use(
    "/error",
    new HttpError("I'm a teapot", Response::CODE_IM_A_TEAPOT)
);

$router->use(
    "/",
    Http::get(fn() => Sptf::testDirectory(__DIR__ . "/src/tests/cases"))
        ->query("_test")
);



$app->serve();
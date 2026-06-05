<?php

// Location locked file

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/hazard/exception-handler.php';

use core\App;
use core\configs\AppConfig;
use core\configs\EnvConfig;
use core\database\sql\connections\MySqlDriver;
use core\database\sql\Sql;
use core\pages\Pages;



$config = new EnvConfig(App::getEnvStatic());
AppConfig::getInstance()->set($config);

Sql::connect(App::DATABASE, new MySqlDriver(
    $config->getSqlConfig()
));

Pages::load();



$routeChasmApp = App::getInstance();

function routechasm_set(App $app): void {
    global $routeChasmApp;
    $routeChasmApp = $app;
}

function routechasm_get(): App {
    global $routeChasmApp;
    return $routeChasmApp;
}

function routechasm_serve(): void {
    global $routeChasmApp;
    $routeChasmApp->serve();
}

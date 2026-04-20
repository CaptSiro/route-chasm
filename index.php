<?php

use core\actions\Assets\Assets;
use core\actions\Assets\policy\ShowExplorerPolicy;
use core\App;
use core\configs\AppConfig;
use core\configs\EnvConfig;
use core\database\sql\connections\MySqlDriver;
use core\database\sql\Sql;
use core\fs\FileServer;
use core\sideloader\SideLoader;
use project\Frame;

require_once __DIR__ ."/src/autoload.php";


$config = new EnvConfig(App::getEnvStatic());
AppConfig::getInstance()->set($config);

Sql::connect(App::DATABASE, new MySqlDriver(
    $config->getSqlConfig()
));



$app = App::getInstance();
$router = $app->getMainRouter();

$router->bind('/fs', FileServer::getInstance());
$router->bind('/import', SideLoader::getInstance()->initRouter($app));
$router->expose('/public', (new Assets(__DIR__ .'/public'))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));

$router->use('/', new Frame());



$app->serve();
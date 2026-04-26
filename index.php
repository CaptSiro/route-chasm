<?php

use components\core\Admin\Home\AdminHome;
use components\core\Search\Search;
use components\docs\Docs;
use components\Home\Home;
use core\actions\Assets\Assets;
use core\actions\Assets\policy\ShowExplorerPolicy;
use core\admin\Admin;
use core\admin\AdminRouter;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\configs\AppConfig;
use core\configs\EnvConfig;
use core\database\sql\connections\MySqlDriver;
use core\database\sql\Sql;
use core\forms\controls\Select\Select;
use core\fs\FileServer;
use core\mounts\StaticMount;
use core\navigation\Navigator;
use core\pages\PageFactory;
use core\pages\Pages;
use core\RouteChasmEnvironment;
use core\sideloader\SideLoader;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

require_once __DIR__ ."/src/autoload.php";



$config = new EnvConfig(App::getEnvStatic());
AppConfig::getInstance()->set($config);

Sql::connect(App::DATABASE, new MySqlDriver(
    $config->getSqlConfig()
));

Pages::load();

$app = App::getInstance();
$router = $app->getMainRouter();



$router->bind('/docs', Docs::getInstance());
$router->bind('/search', Search::getInstance());
$router->bind('/fs', FileServer::getInstance());
$router->bind('/import', SideLoader::getInstance()->initRouter($app));

$router->bind(
    Admin::mount(new StaticMount('admin'), '/admin'),
    AdminRouter::getInstance(new AdminHome())
);



$router->expose('public', (new Assets(__DIR__ .'/public'))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));



$router->use('/', new Home());



Navigator::register(PageFactory::getInstance());

$router->bind(
    Navigator::mount(new StaticMount(RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT), '/'),
    new Navigator()
);



$app->serve();
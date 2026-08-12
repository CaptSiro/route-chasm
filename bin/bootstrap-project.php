<?php

// Location locked file

use components\Admin\Admin;
use components\docs\Docs;
use core\route\Path;
use core\view\PageView;
use example\components\Home;
use components\pages\PageFactory;
use components\Search\Search;
use core\actions\Assets\Assets;
use core\actions\Assets\policy\ShowExplorerPolicy;
use core\fs\FileServer;
use core\mounts\StaticMount;
use core\navigation\Navigator;
use core\RouteChasmEnvironment;
use core\sideloader\SideLoader;

$app = routechasm_get();
$router = $app->getMainRouter();



$router->bind('/docs', Docs::getInstance());
$router->bind('/search', Search::getInstance());
$router->bind('/fs', FileServer::getInstance());
$router->bind('/import', SideLoader::getInstance()->initRouter($app));

$admin = Admin::getInstance();
$router->bind($admin->mount('/admin'), $admin);



// Order dependent
$assetDirectories = [
    project_mounted("<assets>"),
    Path::join(DIRECTORY_FRAMEWORK, 'public')
];

$router->expose('public', (new Assets($assetDirectories))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));



$router->use('/', PageView::fromComponent(new Home()));



Navigator::register(PageFactory::getInstance());

$router->bind(
    Navigator::mount(new StaticMount(RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT), '/'),
    new Navigator()
);

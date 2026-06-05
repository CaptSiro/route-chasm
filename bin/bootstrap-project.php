<?php

// Location locked file

use components\core\Admin\Home\AdminHome;
use components\core\Search\Search;
use components\docs\Docs;
use components\Home\Home;
use core\actions\Assets\Assets;
use core\actions\Assets\policy\ShowExplorerPolicy;
use core\admin\Admin;
use core\admin\AdminRouter;
use core\fs\FileServer;
use core\mounts\StaticMount;
use core\navigation\Navigator;
use core\pages\PageFactory;
use core\RouteChasmEnvironment;
use core\sideloader\SideLoader;

$app = routechasm_get();
$router = $app->getMainRouter();



$router->bind('/docs', Docs::getInstance());
$router->bind('/search', Search::getInstance());
$router->bind('/fs', FileServer::getInstance());
$router->bind('/import', SideLoader::getInstance()->initRouter($app));

$router->bind(
    Admin::mount(new StaticMount('admin'), '/admin'),
    AdminRouter::getInstance(new AdminHome())
);



$router->expose('public', (new Assets(project_mounted("<framework>/public")))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));



$router->use('/', new Home());



Navigator::register(PageFactory::getInstance());

$router->bind(
    Navigator::mount(new StaticMount(RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT), '/'),
    new Navigator()
);

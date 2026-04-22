<?php

use components\core\Admin\Home\AdminHome;
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
use core\fs\FileServer;
use core\fs\FileSystem;
use core\http\HttpCode;
use core\locale\Lexicon;
use core\mounts\StaticMount;
use core\pages\Pages;
use core\RouteChasmEnvironment;
use core\sideloader\SideLoader;
use models\core\fs\File;
use models\core\Setting\Setting;
use project\Frame;
use project\Startuh;
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

$router->bind(
    Admin::mount(new StaticMount('admin'), '/admin'),
    AdminRouter::getInstance(new AdminHome())
);

$router->bind('/fs', FileServer::getInstance());
$router->bind('/import', SideLoader::getInstance()->initRouter($app));
$router->expose('/public', (new Assets(__DIR__ .'/public'))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));

$router->use('/', new Frame());




$router->use('/random-background', function (Request $request, Response $response) {
    $lexicon = Lexicon::group(Startuh::LEXICON_GROUP);

    $start = microtime(true);
    $osDirs = Setting::fromName(
        Startuh::SETTING_BACKGROUND_DIRECTORY_OS,
        true,
        "",
        [PROPERTY_EDITABLE => true]
    )->toString();

    if (!Startuh::backgroundsExist($osDirs)) {
        $response->sendMessage(
            $lexicon->tr('Backgrounds directory is not well defined'),
            HttpCode::SE_INTERNAL_SERVER_ERROR
        );
    }

    $content = '';
    foreach (Startuh::listBackgroundFiles($osDirs) as $fileInfo) {
        $content .= $fileInfo->getFilename();
    }

    $backgrounds = FileSystem::makeDirectory(FileSystem::getRoot(), 'Backgrounds');
    $directoryHash = Setting::fromName(
        Startuh::SETTING_BACKGROUND_DIRECTORY_OS_HASH,
        true,
        "",
        [PROPERTY_EDITABLE => false]
    );

    $contentHash = hash(Startuh::HASH_ALGORITHM, $content);
    if ($directoryHash->toString() !== $contentHash || $request->getUrl()->getQuery()->exists('force')) {
        $directoryHash->value = $contentHash;
        $directoryHash->save();

        foreach (Startuh::listBackgroundFiles($osDirs) as $fileInfo) {
            FileSystem::storeFile($backgrounds, $fileInfo->getRealPath());
        }
    }

    $factory = File::getDescription()
        ->getFactory();
    $image = $factory->firstExecute(
        $factory->randomQuery()
            ->where(File::isChildOfQuery($backgrounds))
            ->where(File::isTypeOfQuery(File::TYPE_IMAGE))
    );

    if (is_null($image)) {
        $response->sendMessage(
            $lexicon->tr('Backgrounds directory is empty'),
            HttpCode::CE_NOT_FOUND
        );
    }

    $response->json([
        "file" => FileServer::getInstance()
            ->createFileUrl($image),
        "timeSpent" => microtime(true) - $start,
    ]);
});



$app->serve();
<?php

namespace core\fs;

use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\Http;
use core\http\HttpCode;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\url\Url;
use models\core\fs\Directory;
use models\core\fs\File;

class FileServer extends Router {
    use Singleton, LexiconUnit;

    const LEXICON_GROUP = FileSystem::LEXICON_GROUP;



    public function __construct() {
        parent::__construct();
        $this->setLexiconGroup(static::LEXICON_GROUP);
    }



    public function createFilePath(File $file): Path {
        return $this->getRoute()->toStaticPath()
            ->append('file')
            ->append($file->hash);
    }

    public function createFileUploadUrl(?Directory $directory = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()->append('file');
        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->getQuery()->load($request->getUrl()->getQuery()->toArray());

        if (!is_null($directory)) {
            $ret->setQueryArgument(
                RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY,
                $directory->getId()
            );
        }

        return $ret;
    }

    public function createFileUrl(File $file): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()
            ->append('file')
            ->append($file->hash);

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->getQuery()->load($request->getUrl()->getQuery()->toArray());
        return $ret;
    }

    public function createDirectoryUrl(?Directory $directory = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()->append('directory');
        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->getQuery()->load($request->getUrl()->getQuery()->toArray());

        if (!is_null($directory)) {
            $ret->setQueryArgument(
                RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY,
                $directory->getId()
            );
        }

        return $ret;
    }



    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        $router->use(
            '/file/',
            Http::post(function (Request $request, Response $response) {
                $directory = Directory::fromRequest($request);

                foreach ($request->getFiles()->toArray() as $file) {
                    FileSystem::storeUploadedFile($directory, $file);
                }

                $response->sendStatus(HttpCode::S_OK);
            }),
        );

        $router->use(
            '/directory/',
            Http::post(function (Request $request, Response $response) {
                $parent = Directory::fromRequest($request);
                $name = $request->getBody()->getStrict('name');

                FileSystem::makeDirectory($parent, $name);

                $response->sendStatus(HttpCode::S_OK);
            }),

            Http::patch(function (Request $request, Response $response) {
                $body = $request->getBody();
                $id = $body->getStrict('id');
                $name = $body->getStrict('name');

                if (is_null($directory = Directory::fromId(intval($id)))) {
                    $response->sendMessage(
                        $this->tr('Could not find directory'),
                        HttpCode::CE_NOT_FOUND
                    );
                }

                $directory->renameEntry($name);
                $response->sendStatus(HttpCode::S_OK);
            }),

            Http::delete(function (Request $request, Response $response) {
                $body = $request->getBody();
                $id = $body->getStrict('id');
                $name = $body->getStrict('name');

                if (is_null($directory = Directory::fromId(intval($id)))) {
                    $response->sendMessage(
                        $this->tr('Could not find directory'),
                        HttpCode::CE_NOT_FOUND
                    );
                }

                $directory->deleteEntry();
                $response->sendStatus(HttpCode::S_OK);
            })
        );

        $router->use(
            '/file/[hash]',
            Http::get(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $response->readFile($file->getRealPath());
            }),

            Http::patch(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $file->renameEntry($request->getBody()->getStrict('name'));
            }),

            Http::delete(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $file->delete();
            })
        );
    }
}
<?php

namespace core\fs;

use components\core\fs\FileVariantTransformers;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\fs\variants\FileVariant;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\url\Url;
use models\core\fs\Directory;
use models\core\fs\File;
use models\core\User\User;

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

    public function createFileUrl(?File $file = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()
            ->append('file');

        if (!is_null($file)) {
            $path->append($file->hash);
        }

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

    public function createVariantUrl(FileVariant $variant): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()
            ->append('variant')
            ->append($variant->getName());

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->getQuery()
            ->load($request->getUrl()->getQuery()->toArray());

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
            '/file/[hash]',
            Http::get(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $name = $file->getFileName();
                $response->setHeader(HttpHeader::CONTENT_DISPOSITION, "inline; filename=\"$name\"");
                $response->setHeader(HttpHeader::CONTENT_TYPE, $file->type);

                $variant = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_FS_VARIANT);
                if (is_null($variant)) {
                    $response->readFile($file->getRealPath());
                    return;
                }

                [$v, $t] = explode(':', $variant);
                if (is_null($fileVariant = FileSystem::getVariant($v))) {
                    $response->readFile($file->getRealPath());
                    return;
                }

                if (is_null($transformer = $fileVariant->getTransformer($t))
                    || !$transformer->supports($file))
                {
                    $response->readFile($file->getRealPath());
                    return;
                }

                $response->readFile($transformer->transform($file));
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

        $router->use(
            '/directory/',
            Http::get(function (Request $request, Response $response) {
                $user = User::fromSession($request->getSession());
                $isAdmin = is_null($user) || $user->isAdmin();
                $response->render(
                    FileSystem::listDirectoryModal(readonly: !$isAdmin)
                );
            }),

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
            '/variant/[variant]',
            Http::get(function (Request $request, Response $response) {
                $variant = FileSystem::getVariant(
                    $request->getParam()->getStrict('variant')
                );

                $query = $request
                    ->getUrl()
                    ->getQuery();

                $name = $query->get('name', 'file-variant-transformers');
                $label = $query->get('label', 'Transformers');

                $response->renderRoot(
                    new FileVariantTransformers(
                        $variant->getTransformers(),
                        $name,
                        $label
                    )
                );
            })
        );
    }
}
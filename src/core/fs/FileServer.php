<?php

namespace core\fs;

use components\core\fs\FileVariantTransformers;
use components\core\Message\Message;
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

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());

        if (!is_null($directory)) {
            $ret->setQueryArgument(
                RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY,
                $directory->id
            );
        }

        return $ret;
    }

    protected function createHashedUrl(string $function, ?File $file = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()
            ->append($function);

        if (!is_null($file)) {
            $path->append($file->hash);
        }

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        return $ret;
    }

    public function createFileUrl(?File $file = null): Url {
        return $this->createHashedUrl('file', $file);
    }

    public function createDownloadUrl(?File $file = null): Url {
        return $this->createHashedUrl('download', $file);
    }

    public function createInfoUrl(?File $file = null): Url {
        return $this->createHashedUrl('info', $file);
    }

    public function createDirectoryUrl(?Directory $directory = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getRoute()->toStaticPath()->append('directory');
        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());

        if (!is_null($directory)) {
            $ret->setQueryArgument(
                RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY,
                $directory->id
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

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        return $ret;
    }



    protected function getTransformedFile(Request $request, File $file): string {
        $variant = $request->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_FS_VARIANT);

        if (is_null($variant)) {
            return $file->getRealPath();
        }

        [$v, $t] = explode(':', $variant);
        if (is_null($fileVariant = FileSystem::getVariant($v))) {
            return $file->getRealPath();
        }

        if (is_null($transformer = $fileVariant->getTransformer($t))
            || !$transformer->supports($file))
        {
            return $file->getRealPath();
        }

        return $transformer->transform($file);
    }

    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        $router->use(
            '/file/',
            Http::post(function (Request $request, Response $response) {
                $directory = Directory::fromRequest($request);

                foreach ($request->getFiles()->toArray() as $file) {
                    $f = $file->getName();
                    switch ($e = $file->getError()) {
                        case UPLOAD_ERR_OK: {
                            if (!is_null(FileSystem::storeUploadedFile($directory, $file))) {
                                break;
                            }

                            $response->sendMessage(
                                "File '$f' uploaded successfully but storing failed",
                                HttpCode::SE_INTERNAL_SERVER_ERROR
                            );
                            break;
                        }

                        case UPLOAD_ERR_INI_SIZE: {
                            $response->sendMessage(
                                "File '$f' is too large",
                                HttpCode::CE_BAD_REQUEST
                            );
                            break;
                        }

                        default: {
                            $response->sendMessage(
                                "File '$f' did not uploaded successfully. Error: $e",
                                HttpCode::CE_BAD_REQUEST
                            );
                            break;
                        }
                    }
                }

                $response->setHeader(HttpHeader::X_RELOAD, 'reload');
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
                $response->setHeaders([
                    HttpHeader::CONTENT_DISPOSITION => "inline; filename=\"$name\"",
                    HttpHeader::CONTENT_TYPE => $file->type
                ]);

                $response->readFile($this->getTransformedFile($request, $file));
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
            '/download/[hash]',
            Http::get(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $name = $request->getUrl()
                    ->getQuery()
                    ->get('name', $file->getFileName());

                $response->setHeaders([
                    HttpHeader::CONTENT_DISPOSITION => "inline; filename=\"$name\"",
                    HttpHeader::CONTENT_TYPE => $file->type
                ]);

                $response->download($this->getTransformedFile($request, $file), $name);
            }),
        );

        $router->use(
            '/info/[hash]',
            Http::get(function (Request $request, Response $response) {
                if (is_null($file = File::fromHash($request->getParam()->getStrict('hash')))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $response->json([
                    'name' => $file->name,
                    'extension' => $file->extension,
                    'fileName' => $file->getFileName(),
                    'type' => $file->type,
                    'size' => $file->size,
                    'sizeHumanReadable' => $file->getHumanReadableSize(),
                    'parent' => $file->getParent()?->getEntryName(),
                    'icon' => $file->getEntryIcon()
                ]);
            }),
        );

        $router->use(
            '/directory/',
            Http::get(function (Request $request, Response $response) {
                $user = User::fromRequest($request);
                $isAdmin = is_null($user) || $user->isAdmin();

                $response->render(
                    FileSystem::listDirectoryModal(
                        fileType: $request->getUrl()
                            ->getQuery()
                            ->get(RouteChasmEnvironment::QUERY_FS_FILE_TYPE),
                        readonly: !$isAdmin,
                    )
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
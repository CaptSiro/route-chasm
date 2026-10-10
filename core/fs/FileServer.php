<?php

namespace core\fs;

use components\fs\FileVariantTransformers;
use core\App;
use core\communication\body\DictionaryBody;
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
use models\fs\Directory;
use models\fs\File;
use models\User\User;

class FileServer extends Router {
    use Singleton, LexiconUnit;

    public const LEXICON_GROUP = FileSystem::LEXICON_GROUP;
    public const QUERY_HASH = 'hash';
    public const FIELD_NAME = 'name';



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



    /**
     * None of the mutating /fs endpoints (upload, rename, delete, mkdir) checked who the
     * caller is, so an anonymous visitor could upload arbitrary files (served back inline from this origin -> stored
     * XSS) or delete/rename any file/directory. The dashboard login only lets admins in, so the same `isAdmin()`
     * rule is used here (matches AiGeneratedPage). Terminates the request with 403 when the check fails.
     */
    protected function requireAdmin(Request $request, Response $response): void {
        $user = User::fromRequest($request);

        if (is_null($user) || !$user->isAdmin()) {
            $response->sendStatus(HttpCode::CE_FORBIDDEN);
        }
    }

    /**
     * Uploaded files are served with the client-supplied MIME type. Without these headers an
     * uploaded text/html or image/svg+xml file runs scripts in this origin when opened directly. `sandbox` CSP only
     * affects documents navigated to directly, so <img>/<video> embedding keeps working. It is applied only to
     * scriptable types, because some browsers refuse to render PDFs inline inside a sandboxed document.
     */
    protected function setUserContentHeaders(Response $response, File $file): void {
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        $type = strtolower($file->type ?? '');
        $isScriptable = str_contains($type, 'html')
            || str_contains($type, 'xml')
            || str_contains($type, 'svg')
            || str_contains($type, 'javascript')
            || in_array(strtolower($file->extension ?? ''), ['html', 'htm', 'xhtml', 'svg', 'xml', 'js', 'mjs'], true);

        if ($isScriptable) {
            $response->setHeader('Content-Security-Policy', 'sandbox');
        }
    }

    /**
     * Strips characters that would break out of the quoted `filename="..."` parameter.
     */
    protected static function sanitizeHeaderFileName(string $name): string {
        return str_replace(['"', '\\', "\r", "\n"], '_', $name);
    }

    protected function getTransformedFile(Request $request, File $file): string {
        $variant = $request->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_FS_VARIANT);

        if (is_null($variant)) {
            return $file->getRealPath();
        }

        // [Claude review] explode() returns a single element when ':' is missing, so `?v=foo` emitted an
        // "Undefined array key 1" warning which the global error handler turns into a 500. Pad to two elements.
        [$v, $t] = array_pad(explode(':', $variant, 2), 2, '');
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
                $messageNoFilesSent = $this->tr('No files sent. You need to send at least one file');
                $messageFileStoringFailed = $this->crt("File '{}' uploaded successfully but storing failed");
                $messageFileTooLarge = $this->crt("File '{}' is too large");
                $messageFileDidNotUpload = $this->crt("File '{}' did not uploaded successfully.");

                $this->requireAdmin($request, $response);

                $directory = Directory::fromRequest($request);

                $files = $request
                    ->body(DictionaryBody::class)
                    ->getFiles()
                    ->toArray();

                if (empty($files)) {
                    $response->sendMessage($messageNoFilesSent, HttpCode::CE_BAD_REQUEST);
                }

                foreach ($files as $file) {
                    $f = $file->getName();
                    switch ($e = $file->getError()) {
                        case UPLOAD_ERR_OK: {
                            if (!is_null(FileSystem::storeUploadedFile($directory, $file))) {
                                break;
                            }

                            $response->sendMessage(
                                $messageFileStoringFailed->format($f),
                                HttpCode::SE_INTERNAL_SERVER_ERROR
                            );
                            break;
                        }

                        case UPLOAD_ERR_INI_SIZE: {
                            $response->sendMessage(
                                $messageFileTooLarge->format($f),
                                HttpCode::CE_BAD_REQUEST
                            );
                            break;
                        }

                        default: {
                            $response->sendMessage(
                                $messageFileDidNotUpload->format($f) . ' Error: ' . $e,
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
                if (is_null($file = File::fromRequest($request))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $name = self::sanitizeHeaderFileName($file->getFileName());
                $this->setUserContentHeaders($response, $file);
                $response->setHeaders([
                    HttpHeader::CONTENT_DISPOSITION => "inline; filename=\"$name\"",
                    HttpHeader::CONTENT_TYPE => $file->type
                ]);

                $response->readFile($this->getTransformedFile($request, $file));
            }),

            Http::patch(function (Request $request, Response $response) {
                $this->requireAdmin($request, $response);

                if (is_null($file = File::fromRequest($request))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                // [Claude review] Was ->getFiles(): `name` is a plain field (fs.js sends JSON { id, name }), so
                // getStrict('name') on the files map always threw NotDefinedException.
                $file->renameEntry($request
                    ->body(DictionaryBody::class)
                    ->getFields()
                    ->getStrict('name'));
            }),

            Http::delete(function (Request $request, Response $response) {
                $this->requireAdmin($request, $response);

                if (is_null($file = File::fromRequest($request))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $file->delete();
            })
        );

        $router->use(
            '/download/[hash]',
            Http::get(function (Request $request, Response $response) {
                if (is_null($file = File::fromRequest($request))) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $name = self::sanitizeHeaderFileName($request->getUrl()
                    ->getQuery()
                    ->get('name', $file->getFileName()));

                $this->setUserContentHeaders($response, $file);
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
                if (is_null($file = File::fromRequest($request))) {
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
                // [Claude review] Was `is_null($user) || $user->isAdmin()`, which granted the editable (non-readonly)
                // view when no user could be resolved. Unknown user must mean "not admin".
                $isAdmin = !is_null($user) && $user->isAdmin();

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
                $this->requireAdmin($request, $response);

                $parent = Directory::fromRequest($request);
                $name = $request
                    ->body(DictionaryBody::class)
                    ->getFields()
                    ->getStrict('name');

                FileSystem::makeDirectory($parent, $name);

                $response->sendStatus(HttpCode::S_OK);
            }),

            Http::patch(function (Request $request, Response $response) {
                $this->requireAdmin($request, $response);

                $messageDirectoryNotFound = $this->tr('Could not find directory');

                $fields = $request
                    ->body(DictionaryBody::class)
                    ->getFields();

                $id = $fields->getStrict('id');
                $name = $fields->getStrict('name');

                if (is_null($directory = Directory::fromId(intval($id)))) {
                    $response->sendMessage($messageDirectoryNotFound, HttpCode::CE_NOT_FOUND);
                }

                $directory->renameEntry($name);
                $response->sendStatus(HttpCode::S_OK);
            }),

            Http::delete(function (Request $request, Response $response) {
                $this->requireAdmin($request, $response);

                $messageDirectoryNotFound = $this->tr('Could not find directory');

                $fields = $request
                    ->body(DictionaryBody::class)
                    ->getFields();

                $id = $fields->getStrict('id');
                $name = $fields->getStrict('name');

                if (is_null($directory = Directory::fromId(intval($id)))) {
                    $response->sendMessage($messageDirectoryNotFound, HttpCode::CE_NOT_FOUND);
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

                $response->render(
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
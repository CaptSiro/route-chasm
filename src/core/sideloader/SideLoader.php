<?php

namespace core\sideloader;

use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\module\Loader;
use core\patterns\Ident;
use core\route\Path;
use core\route\Router;
use core\sideloader\api\SideLoaderApi;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\Singleton;
use core\utils\Files;
use core\view\BufferTransform;
use core\view\Renderer;
use core\view\View;
use models\core\Setting\Setting;
use models\core\SideLoaderRecord;

class SideLoader implements View {
    use Renderer, Singleton;



    public const IDENTIFIER = 'route-chasm-core:side-loader';

    public const SETTING_HASH_LENGTH = self::IDENTIFIER . '_hash-length';
    public const SETTING_MAX_RETRIES = self::IDENTIFIER . '_max-retries';
    public const SETTING_ADD_FILE_NAMES = self::IDENTIFIER . '_add-file-names';

    public const FILE_SEPARATOR = ',';
    public const DIRECTORY_MERGED = 'merged';
    public const HEADER_X_REQUIRE = 'X-Require';
    public const IMPORTER_CSS_CLASS = 'side-loader-importer';

    /**
     * If <code>QUERY_FORCE</code> is present in url query the default response type checking is ignored and
     * <code>HEADER_X_REQUIRE</code> will always be set on response
     */
    public const QUERY_FORCE = 's';
    public const TEMPLATE_PLACEHOLDER = '<!-- '. self::IDENTIFIER .' -->';



    public static function getApi(): View {
        $instance = self::getInstance();
        return new SideLoaderApi(
            App::getInstance()->attach($instance->router->getRoute()->toStaticPath())
        );
    }



    protected array $files;
    /**
     * @var array<string, Importer> $importers
     */
    protected array $importers;
    protected Router $router;
    protected bool $hasBeenRendered;

    protected Setting $hashLength;
    protected Setting $maxRetries;
    protected bool $initialized;



    public function __construct() {
        $this->files = [];

        $this->addImporter(new Javascript());
        $this->addImporter(new Css());

        $this->router = new Router();
        $this->initialized = false;
        $this->hasBeenRendered = false;

        $this->hashLength = Setting::fromName(
            self::SETTING_HASH_LENGTH,
            true,
            4
        );

        $retries = Setting::fromName(self::SETTING_MAX_RETRIES);
        if ($retries === null) {
            $retries = new Setting();

            $retries->name = self::SETTING_MAX_RETRIES;
            $retries->value = 128;
            $retries->editable = true;

            $retries->save();
        }

        $this->maxRetries = $retries;
    }



    public function addImporter(Importer $importer): void {
        $this->importers[$importer->getFileExtension()] = $importer;
    }

    public function doSendRequireHeader(Request $request): bool {
        // todo
        //  investigate
//        $format = App::getInstance()
//            ->getResponse()
//            ->getFormat($request);
//
//        return $format !== Format::IDENT_HTML
//            || $request->getUrl()->getQuery()->exists(RouteChasmEnvironment::QUERY_SIDELOADER_FORCE);

        return true;
    }

    public function isInitialized(): bool {
        return $this->initialized;
    }

    public function initRouter(Loader $loader): Router {
        $loader->on(Response::EVENT_OB_TRANSFORM, function (BufferTransform $buffer) {
            if (!$this->hasBeenRendered) {
                return;
            }

            $replacement = '';

            foreach ($this->files as $type => $files) {
                if (!isset($this->importers[$type])) {
                    continue;
                }

                $replacement .= $this->importers[$type]
                    ->setFiles($files)
                    ->render();
            }

            $buffer->setContents(
                str_replace(self::TEMPLATE_PLACEHOLDER, $replacement, $buffer->getContents())
            );
        });

        $loader->on(Response::EVENT_HEADERS_GENERATION, function (Response $response) {
            if ($response->hasHeader(self::HEADER_X_REQUIRE)) {
                return;
            }

            if (!$this->doSendRequireHeader(App::getInstance()->getRequest())) {
                return;
            }

            $require = '';

            foreach ($this->files as $type => $files) {
                $hashed = $this->joinHashed($files);
                if ($hashed === '') {
                    continue;
                }

                $require .= "$type($hashed);";
            }

            if ($require === '') {
                return;
            }

            $response->setHeader(self::HEADER_X_REQUIRE, $require);
        });

        $this->router->use(
            '/',
            Http::get(function (Request $request, Response $response) {
                $type = $request->getUrl()->getQuery()->getStrict('type');
                if (!isset($this->importers[$type])) {
                    $response->sendMessage(
                        "There is not known file importer for type '$type'",
                        HttpCode::CE_BAD_REQUEST
                    );
                    return;
                }

                $importer = $this->importers[$type];
                $response->setHeaders([
                    Cors::ORIGIN => "*",
                    HttpHeader::CONTENT_TYPE => $importer->getFileMimeType()
                ]);

                $response->send($importer->begin(), false);
                $files = $request->getUrl()->getQuery()->getStrict('files');

                if (!str_contains($files, self::FILE_SEPARATOR)) {
                    $entry = SideLoaderRecord::fromHash($files);
                    if (is_null($entry)) {
                        $response->sendMessage(
                            "File not found (file hash: '$files')",
                            HttpCode::CE_NOT_FOUND
                        );
                    }

                    $response->send($importer->fileHead($entry->path), false);
                    $response->readFile($entry->path);
                }

                foreach (explode(self::FILE_SEPARATOR, $files) as $hash) {
                    $entry = SideLoaderRecord::fromHash($hash);
                    if (is_null($entry)) {
                        continue;
                    }

                    $response->send($importer->fileHead($entry->path), false);
                    $response->readFile($entry->path, doFlush: false);
                }

                $response->send($importer->end(), false);
                $response->flush();
            })
                ->query('type', Ident::getInstance())
                ->query('files')
        );

        $this->initialized = true;
        return $this->router;
    }

    public function joinHashed(array $files): string {
        $hashed = '';
        $first = true;
        $length = $this->hashLength->toInt();

        foreach (array_unique($files) as $file) {
            $real = realpath($file);

            if (!file_exists($real)) {
                continue;
            }

            $entry = SideLoaderRecord::fromPath($real);
            if (is_null($entry)) {
                $entry = new SideLoaderRecord();
                $entry->hash = SideLoaderRecord::generateHash($this->maxRetries->toInt(), $length);
                $entry->path = $real;
                $entry->save();
            }

            if (!$first) {
                $hashed .= self::FILE_SEPARATOR;
            }

            $hashed .= $entry->hash;
            $first = false;
        }

        if ($length !== $this->hashLength->toInt()) {
            $this->hashLength->value = $length;
            $this->hashLength->save();
        }

        return $hashed;
    }

    public function hashPaths(array $files): string {
        $first = true;
        $buffer = "";

        foreach ($files as $file) {
            $hash = Files::hashPath($file);
            if ($hash === false) {
                continue;
            }

            if (!$first) {
                $buffer .= ',';
            }

            $first = false;
            $buffer .= dechex($hash);
        }

        return $buffer;
    }

    public function createImportUrl(string $type, array $files): string {
        $path = App::getInstance()->attach($this->router->getRoute()->toStaticPath());

        $url = App::getInstance()
            ->getRequest()
            ->getUrl()
            ->copy();

        $url->getQuery()->clear();

        return $url
            ->setPath(Path::from($path))
            ->setQueryArgument('type', $type)
            ->setQueryArgument('files', $this->joinHashed($files))
            ->toString();
    }

    public function createSourceAttribute(string $type, array $files, string $attribute = 'src'): string {
        $class = self::IMPORTER_CSS_CLASS;
        $url = $this->createImportUrl($type, $files);
        $files = $this->hashPaths($files);

        return 'class="'. $class
            .'" data-type="' . $type
            . '" data-files="'. $files
            .'" '. $attribute .'="'. $url .'"';
    }

    public function import(string $type, string $file): void {
        if (!isset($this->files[$type])) {
            $this->files[$type] = [$file];
            return;
        }

        $this->files[$type][] = $file;
    }

    function render(): string {
        $this->hasBeenRendered = true;
        return self::TEMPLATE_PLACEHOLDER;
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}
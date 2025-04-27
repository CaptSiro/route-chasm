<?php

namespace modules\SideLoader;

use core\App;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Sql;
use core\fs\Glob;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\module\DatabaseMigration;
use core\module\DefaultModule;
use core\module\Loader;
use core\module\ModuleInfo;
use core\patterns\Ident;
use core\Router;
use core\Singleton;
use core\ResourceLoader;
use core\url\UrlBuilder;
use core\utils\Arrays;
use core\utils\Files;
use core\view\BufferTransform;
use core\view\View;
use models\core\Setting\Setting;
use modules\SideLoader\Api\Api;
use modules\SideLoader\FileImporter\FileImporter;

class SideLoader extends DefaultModule implements View {
    use ResourceLoader, Singleton;

    public const IDENTIFIER = 'route-chasm-core:side-loader';
    public const VERSIONS = ['v1'];

    public const SETTING_HASH_LENGTH = self::IDENTIFIER . '_hash-length';
    public const SETTING_MAX_RETRIES = self::IDENTIFIER . '_max-retries';

    public const FILE_SEPARATOR = ',';
    public const DIRECTORY_MERGED = 'merged';
    public const HEADER_X_REQUIRE = 'X-Require';
    public const IMPORTER_CSS_CLASS = 'side-loader-importer';

    /**
     * If <code>FORCE_QUERY</code> is present in url query the default response type checking is ignored and
     * <code>HEADER_X_REQUIRE</code> will always be set on response
     */
    public const QUERY_FORCE = 's';
    public const TEMPLATE_PLACEHOLDER = '<!-- '. self::IDENTIFIER .' -->';



    public static function getApi(): View {
        $instance = self::getInstance();
        $instance->accessibleAfterLoad();
        return new Api(App::getInstance()->prependHome($instance->router->getUrlPath()));
    }



    protected array $files;
    /**
     * @var array<string, FileImporter> $fileImporters
     */
    protected array $fileImporters;
    protected Router $router;
    protected bool $hasBeenRendered;

    protected Setting $hashLength;
    protected Setting $maxRetries;



    public function __construct() {
        $this->files = [];

        $javascript = new FileImporter();
        $this->addImporter(
            Javascript::FILE_TYPE,
            $javascript
                ->setFileType(Javascript::FILE_MIME_TYPE)
                ->setTemplate($javascript->getResource("JavascriptImporter.phtml"))
        );

        $css = new FileImporter();
        $this->addImporter(
            Css::FILE_TYPE,
            $css
                ->setFileType(Css::FILE_MIME_TYPE)
                ->setTemplate($css->getResource("CssImporter.phtml"))
        );

        $this->router = new Router();
        $this->hasBeenRendered = false;
    }



    public function getInfo(): ModuleInfo {
        return new ModuleInfo(
            self::IDENTIFIER,
            Arrays::last(self::VERSIONS)
        );
    }

    public function migrate(string $fromVersion): void {
        $database = new DatabaseMigration(
            Sql::getConnection(App::DATABASE),
            self::VERSIONS,
            new Glob(
                $this->getResource('sql'),
                '.sql',
                true
            )
        );

        $database->migrateDatabase($fromVersion, Arrays::last(self::VERSIONS));
    }



    public function addImporter(string $fileType, FileImporter $importer): void {
        $this->fileImporters[$fileType] = $importer;
    }

    public function doSendRequireHeader(Request $request): bool {
        $type = App::getInstance()
            ->getResponse()
            ->getFormat($request);

        return $type !== Format::IDENT_HTML
            || $request->getUrl()->getQuery()->exists(self::QUERY_FORCE);
    }

    public function load(Loader $loader): void {
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

        $loader->on(Response::EVENT_OB_TRANSFORM, function (BufferTransform $buffer) {
            if (!$this->hasBeenRendered) {
                return;
            }

            $replacement = '';

            foreach ($this->files as $type => $files) {
                if (!isset($this->fileImporters[$type])) {
                    continue;
                }

                $replacement .= $this->fileImporters[$type]
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

                $require .= "$type($hashed)";
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
                if (!isset($this->fileImporters[$type])) {
                    $response->sendMessage(
                        "There is not known file importer for type '$type'",
                        HttpCode::CE_BAD_REQUEST
                    );
                    return;
                }

                $response->setHeaders([
                    Cors::ORIGIN => "*",
                    HttpHeader::CONTENT_TYPE => $this->fileImporters[$type]->getFileType()
                ]);

                $files = $request->getUrl()->getQuery()->getStrict('files');
                if (!str_contains($files, self::FILE_SEPARATOR)) {
                    $entry = CacheRecord::fromHash($files);
                    if (is_null($entry)) {
                        $response->sendMessage(
                            "File not found (file hash: '$files')",
                            HttpCode::CE_NOT_FOUND
                        );
                    }

                    $response->readFile($entry->path);
                }

                foreach (explode(self::FILE_SEPARATOR, $files) as $hash) {
                    $entry = CacheRecord::fromHash($hash);
                    if (!is_null($entry)) {
                        $response->readFile($entry->path, doFlush: false);
                    }
                }

                $response->flush();
            })
                ->query('type', Ident::getInstance())
                ->query('files')
        );

        $loader
            ->getMainRouter()
            ->bind('/import', $this->router);

        $this->markLoaded();
    }

    public function joinHashed(array $files): string {
        $this->accessibleAfterLoad();

        $hashed = '';
        $first = true;
        $length = $this->hashLength->toInt();

        foreach ($files as $file) {
            $real = realpath($file);

            if (!file_exists($real)) {
                continue;
            }

            $entry = CacheRecord::fromPath($real);
            if (is_null($entry)) {
                $entry = new CacheRecord();
                $entry->hash = CacheRecord::generateHash($this->maxRetries->toInt(), $length);
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
        $this->accessibleAfterLoad();

        $path = App::getInstance()
            ->prependHome($this->router->getUrlPath());

        return (new UrlBuilder(path: $path))
            ->setQuery('type', $type)
            ->setQuery('files', $this->joinHashed($files))
            ->build();
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
        $this->accessibleAfterLoad();

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
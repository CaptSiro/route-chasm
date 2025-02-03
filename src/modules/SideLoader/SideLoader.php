<?php

namespace modules\SideLoader;

use components\core\HttpError\HttpError;
use core\App;
use core\cache\Cache;
use core\cache\LazyFileCache;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\module\DefaultModule;
use core\module\Loader;
use core\patterns\Ident;
use core\Router;
use core\Singleton;
use core\Source;
use core\url\UrlBuilder;
use core\utils\Files;
use core\utils\Strings;
use core\view\BufferTransform;
use core\view\Render;
use modules\SideLoader\Api\Api;
use modules\SideLoader\FileImporter\FileImporter;

class SideLoader extends DefaultModule implements Render {
    use Source;
    use Singleton;

    public const FILE_SEPARATOR = ',';
    public const FILE_CACHE = 'cache';
    public const DIRECTORY_MERGED = 'merged';
    public const HEADER_X_REQUIRE = 'X-Require';
    public const IMPORTER_CSS_CLASS = 'side-loader-importer';

    /**
     * If <code>FORCE_QUERY</code> is present in url query the default response type checking is ignored and
     * <code>HEADER_X_REQUIRE</code> will always be set on response
     */
    public const QUERY_FORCE = 's';
    public const TEMPLATE_PLACEHOLDER = '<!-- side-loader -->';



    public static function getApi(): Render {
        $instance = self::getInstance();
        $instance->accessibleAfterLoad();
        return new Api(App::getInstance()->prependHome($instance->router->getUrlPath()));
    }



    protected array $files;
    /**
     * @var array<string, FileImporter> $fileImporters
     */
    protected array $fileImporters;
    protected Cache $cache;
    protected Router $router;
    protected bool $hasBeenRendered;



    public function __construct() {
        $this->files = [];
        $this->cache = new LazyFileCache($this->getSource(self::FILE_CACHE));

        $javascript = new FileImporter();
        $this->addImporter(
            Javascript::FILE_TYPE,
            $javascript
                ->setFileType(Javascript::FILE_MIME_TYPE)
                ->setTemplate($javascript->getSource("JavascriptImporter.phtml"))
        );

        $css = new FileImporter();
        $this->addImporter(
            Css::FILE_TYPE,
            $css
                ->setFileType(Css::FILE_MIME_TYPE)
                ->setTemplate($css->getSource("CssImporter.phtml"))
        );

        $this->router = new Router();
        $this->hasBeenRendered = false;
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

        $loader->on(App::EVENT_SHUTDOWN, fn() => $this->cache->save());

        $this->router->use(
            '/',
            Http::get(function (Request $request, Response $response) {
                $type = $request->getUrl()->getQuery()->getStrict('type');
                if (!isset($this->fileImporters[$type])) {
                    $response->render(new HttpError(
                        "There is not known file importer for type '$type'",
                        HttpCode::CE_BAD_REQUEST
                    ));
                    return;
                }

                $response->setHeaders([
                    Cors::ORIGIN => "*",
                    HttpHeader::CONTENT_TYPE => $this->fileImporters[$type]->getFileType()
                ]);

                $files = $request->getUrl()->getQuery()->getStrict('files');
                if (!str_contains($files, self::FILE_SEPARATOR)) {
                    if (!$this->cache->has($files)) {
                        $response->setHeader('X-Debug', $this->cache->asString());
                        $response->render(new HttpError(
                            "File not found (file hash: '$files')",
                            HttpCode::CE_NOT_FOUND
                        ));
                    }

                    $response->readFile($this->cache->get($files));
                }

                foreach (explode(self::FILE_SEPARATOR, $files) as $hash) {
                    if ($this->cache->has($hash)) {
                        $response->readFile($this->cache->get($hash), doFlush: false);
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

        foreach ($files as $file) {
            $hash = Files::hashPath($file, $real);
            if ($hash === false) {
                continue;
            }

            $hex = dechex($hash);
            if (!$this->cache->has($hex)) {
                $this->cache->set($hex, $real);
            }

            if (!$first) {
                $hashed .= self::FILE_SEPARATOR;
            }

            $hashed .= $hex;
            $first = false;
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

    public function getMergedFiles(string $merged): string {
        $this->accessibleAfterLoad();

        if ($this->cache->has($merged)) {
            return $this->cache->get($merged);
        }

        $directory = $this->getSource('merged');
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $mergedFile = dechex((Strings::hashAscii($directory .'/'. $merged) << 16) ^ time());

        $source = $this->getSource("merged/$mergedFile");
        $file = fopen($source, 'w');

        foreach (explode(self::FILE_SEPARATOR, $merged) as $hash) {
            if (!$this->cache->has($hash)) {
                fclose($file);
                unlink($source);

                App::getInstance()
                    ->getResponse()
                    ->render(new HttpError(
                        "File not found (file hash: '$hash')",
                        HttpCode::CE_NOT_FOUND
                    ));
            }

            fwrite($file, file_get_contents($this->cache->get($hash)));
        }

        fclose($file);
        $this->cache->set($merged, realpath($source));
        return $source;
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

    function render(?string $template = null): string {
        $this->hasBeenRendered = true;
        return self::TEMPLATE_PLACEHOLDER;
    }

    public function __toString(): string {
        return $this->render();
    }
}
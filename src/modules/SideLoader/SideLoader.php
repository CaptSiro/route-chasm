<?php

namespace modules\SideLoader;

use components\core\HttpError\HttpError;
use core\App;
use core\cache\Cache;
use core\cache\LazyFileCache;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\module\AvailableAfterLoad;
use core\module\Loader;
use core\module\Module;
use core\Render;
use core\Request;
use core\Response;
use core\Router;
use core\Singleton;
use core\TemplateRenderer;
use core\url\UrlBuilder;
use core\utils\Files;
use core\utils\Strings;
use modules\SideLoader\FileImporter\FileImporter;
use patterns\Ident;

class SideLoader implements Module, Render {
    use AvailableAfterLoad;
    use TemplateRenderer;
    use Singleton;

    public const FILE_SEPARATOR = ',';
    public const FILE_CACHE = 'cache';
    public const DIRECTORY_MERGED = 'merged';
    public const HEADER_X_REQUIRE = 'X-Require';



    protected array $files;
    /**
     * @var array<string, FileImporter> $fileImporters
     */
    protected array $fileImporters;
    protected Cache $cache;
    protected Router $router;



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
        $this->router->use(
            '/',
            Http::get(function (Request $request, Response $response) {
                $type = $request->url->query->getStrict('type');
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

                $files = $request->url->query->getStrict('files');
                if (!str_contains($files, self::FILE_SEPARATOR)) {
                    if (!$this->cache->has($files)) {
                        $response->render(new HttpError("File not found (file hash: '$files')", HttpCode::CE_NOT_FOUND));
                    }

                    $response->readFile($this->cache->get($files));
                }

                $source = $this->getMergedFiles($files);
                $response->readFile($source);
            })
                ->query('type', Ident::getInstance())
                ->query('files')
        );
    }



    public function addImporter(string $fileType, FileImporter $importer): void {
        $this->fileImporters[$fileType] = $importer;
    }

    public function load(Loader $loader): void {
        $loader->on(Response::EVENT_HEADERS_GENERATION, function (Response $response) {
            if ($response->hasHeader(self::HEADER_X_REQUIRE)) {
                return;
            }

            $type = App::getInstance()
                ->getRequest()
                ->getResponseType();

            if ($type === Response::TYPE_HTML) {
                return;
            }

            $require = '';

            foreach ($this->files as $type => $files) {
                $hashed = $this->merge($files);
                if ($hashed === '') {
                    continue;
                }

                $require .= "$type($hashed)";
            }

            $response->setHeader(self::HEADER_X_REQUIRE, $require);
        });

        $loader->on(App::EVENT_SHUTDOWN, fn() => $this->cache->save());

        $loader
            ->getMainRouter()
            ->bind('/import', $this->router);

        $this->markLoaded();
    }

    public function merge(array $files): string {
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

            $hashed .= ($first ? '' : self::FILE_SEPARATOR) . $hex;
            $first = false;
        }

        return $hashed;
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
            ->setQuery('files', $this->merge($files))
            ->build();
    }

    public function import(string $type, string $file): void {
        $this->accessibleAfterLoad();

        if (!isset($this->files[$type])) {
            $this->files[$type] = [$file];
            return;
        }

        $this->files[$type][] = $file;
    }
}
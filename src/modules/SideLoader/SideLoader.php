<?php

namespace modules\SideLoader;

use components\core\HttpError\HttpError;
use core\App;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\Loader;
use core\Module;
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
    use TemplateRenderer;
    use Singleton;

    public const FILE_SEPARATOR = ',';
    public const FILE_CACHE = 'cache.json';
    public const DIRECTORY_MERGED = 'merged';
    public const HEADER_X_REQUIRE = 'X-Require';



    protected array $files;
    /**
     * @var array<string, FileImporter> $fileImporters
     */
    protected array $fileImporters;
    protected array $cache;
    protected bool $cacheUpdated;
    protected Router $router;



    public function __construct() {
        $cacheFile = $this->getSource(self::FILE_CACHE);
        if (!file_exists($cacheFile)) {
            $this->cache = [];
        } else {
            $this->cache = json_decode(file_get_contents($cacheFile), associative: true);
        }

        $this->cacheUpdated = false;

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
                    if (!isset($this->cache[$files])) {
                        $response->render(new HttpError("File not found (file hash: '$files')", HttpCode::CE_NOT_FOUND));
                    }

                    $response->readFile($this->cache[$files]);
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

        $loader->on(App::EVENT_SHUTDOWN, function () {
            if ($this->cacheUpdated) {
                file_put_contents(
                    $this->getSource(self::FILE_CACHE),
                    json_encode($this->cache)
                );
            }
        });

        $loader
            ->getMainRouter()
            ->bind('/import', $this->router);
    }

    public function merge(array $files): string {
        $hashed = '';
        $first = true;

        foreach ($files as $file) {
            $hash = Files::hashPath($file, $real);
            if ($hash === false) {
                continue;
            }

            $base64Hash = Strings::encodeBase64Safe($hash);
            if (!isset($this->cache[$base64Hash])) {
                $this->cache[$base64Hash] = $real;
                $this->cacheUpdated = true;
            }

            $hashed .= ($first ? '' : self::FILE_SEPARATOR) . $base64Hash;
            $first = false;
        }

        return $hashed;
    }

    public function getMergedFiles(string $merged): string {
        if (isset($this->cache[$merged])) {
            return $this->cache[$merged];
        }

        $directory = $this->getSource('merged');
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $mergedFile = Strings::encodeBase64Safe((Strings::hashAscii($directory .'/'. $merged) << 16) ^ time());

        $source = $this->getSource("merged/$mergedFile");
        $file = fopen($source, 'w');

        foreach (explode(self::FILE_SEPARATOR, $merged) as $hash) {
            if (!isset($this->cache[$hash])) {
                fclose($file);
                unlink($source);

                App::getInstance()
                    ->getResponse()
                    ->render(new HttpError(
                        "File not found (file hash: '$hash')",
                        HttpCode::CE_NOT_FOUND
                    ));
            }

            fwrite($file, file_get_contents($this->cache[$hash]));
        }

        fclose($file);
        $this->cache[$merged] = realpath($source);
        $this->cacheUpdated = true;
        return $source;
    }

    public function createImportUrl(string $type, array $files): string {
        $path = App::getInstance()
            ->prependHome($this->router->getUrlPath());

        return (new UrlBuilder(path: $path))
            ->setQuery('type', $type) // todo investigate
            ->setQuery('files', $this->merge($files))
            ->build();
    }

    public function import(string $type, string $file): void {
        if (!isset($this->files[$type])) {
            $this->files[$type] = [$file];
            return;
        }

        $this->files[$type][] = $file;
    }
}
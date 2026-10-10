<?php

namespace core\sideloader;

use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\Cors;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\locale\LexiconUnit;
use core\module\Loader;
use core\route\Path;
use core\route\Router;
use core\sideloader\api\SideLoaderApi;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\Singleton;
use core\storage\Data;
use core\utils\Files;
use core\utils\Regex;
use core\view\BufferTransform;
use core\view\Html;
use core\view\View;
use core\view\ViewTemplateRenderer;
use models\Setting\Setting;
use models\SideLoaderRecord;
use RuntimeException;

class SideLoader implements View {
    use ViewTemplateRenderer, Singleton, LexiconUnit;



    public const LEXICON_UNIT = 'sideloader';
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
     * Per-request memo of real path => record, filled by joinHashed()
     * @var array<string, SideLoaderRecord>
     */
    protected array $records = [];
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
        $this->setLexiconGroup(self::LEXICON_UNIT);

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
                $messageUnknownImporter = $this->crt("There is not known file importer for type '{}'");
                $messageFileNotFound = $this->crt("File not found (file hash: '{}')");

                $type = $request->getUrl()->getQuery()->getStrict('type');
                if (!isset($this->importers[$type])) {
                    $response->sendMessage(
                        $messageUnknownImporter->format($type),
                        HttpCode::CE_BAD_REQUEST
                    );
                    return;
                }

                $importer = $this->importers[$type];
                $response->setHeaders([
                    Cors::ORIGIN => "*",
                    HttpHeader::CONTENT_TYPE => $importer->getFileMimeType()
                ]);

                $files = $request->getUrl()->getQuery()->getStrict('files');

                // [Claude review] The single-file case used to:
                //  - answer 404 only after begin() had already been echoed, so setting the status emitted a
                //    "headers already sent" warning that the global error handler turned into a 500 page;
                //  - call readFile() with flush, which exited before $importer->end(), so a single-file JS import
                //    never dispatched 'scriptLoad' and std_onLoad() callbacks in that file never ran;
                //  - call readFile() on a missing file -> sendMessage() mid-stream (same 500 as above).
                // Unknown single hashes are now rejected before any output, and the file goes through the same
                // loop as multi-file imports (which also skips entries whose file is gone from disk).
                if (!str_contains($files, self::FILE_SEPARATOR) && is_null(SideLoaderRecord::fromHash($files))) {
                    $response->sendMessage(
                        $messageFileNotFound->format($files),
                        HttpCode::CE_NOT_FOUND
                    );
                }

                $response->send($importer->begin(), false);

                foreach (explode(self::FILE_SEPARATOR, $files) as $hash) {
                    $entry = SideLoaderRecord::fromHash($hash);
                    // [Claude review] is_file() check added: readFile() on a missing file calls sendMessage(), which
                    // cannot change the status after output started (see above) and aborted the whole bundle.
                    if (is_null($entry) || !is_file($entry->path)) {
                        continue;
                    }

                    $response->send($importer->fileHead($entry->path), false);
                    if (!$response->readFile($entry->path, doFlush: false)) {
                        // Catch and log into analytics, same above ^
                    }
                }

                $response->send($importer->end(), false);
                $response->flush();
            })
                ->query('type', Regex::PATTERN_IDENT)
                ->query('files')
        );

        $this->initialized = true;
        return $this->router;
    }

    public function joinHashed(array $files): string {
        $hashed = '';
        $first = true;
        $length = $this->hashLength->toInt();

        // [Claude review] Performance: resolve all paths first and fetch their records with ONE query, memoized for
        // the rest of the request. Previously each file ran its own `WHERE path = ?` query, and joinHashed() runs
        // twice per type (X-Require header + importer URL), so the home page issued 30 such queries.
        // realpath() returning false already means the file does not exist, so the extra file_exists() was dropped.
        $reals = [];
        foreach (array_unique($files) as $file) {
            if (($real = realpath($file)) !== false) {
                $reals[] = $real;
            }
        }

        $missing = array_values(array_filter($reals, fn($real) => !isset($this->records[$real])));
        if (!empty($missing)) {
            $this->records = array_merge($this->records, SideLoaderRecord::fromPaths($missing));
        }

        foreach (array_unique($reals) as $real) {
            $entry = $this->records[$real] ?? null;
            if (is_null($entry)) {
                $entry = new SideLoaderRecord();
                $entry->hash = SideLoaderRecord::generateHash($this->maxRetries->toInt(), $length);
                $entry->path = $real;
                $entry->save();
                $this->records[$real] = $entry;
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
        if (empty($file) || is_dir($file)) {
            throw new RuntimeException("File is not either defined or it is directory: '" . Html::escape($file) . "'");
        }
        
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
<?php

namespace components\docs;

use components\ai\DocumentGeneration\DocumentGeneration;
use components\ai\FragmentGeneration\FragmentGeneration;
use components\ai\InputMessage;
use components\ai\Schema\ArraySchema;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Icon;
use components\core\Search\SearchResult;
use components\core\Search\SearchResults;
use core\actions\Block;
use core\ai\Client;
use core\ai\clients\OpenAi;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\Http;
use core\http\HttpCode;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\url\Url;
use core\utils\Files;
use DirectoryIterator;
use models\core\Language\Language;
use models\core\Privilege\Privilege;
use models\core\Setting\Setting;
use models\core\UserResource;
use models\docs\Document;
use models\docs\Fragment;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Docs extends Router {
    use Singleton;



    public const LEXICON_GROUP = 'docs';

    public const PROPERTY_DOCUMENT_CONTENT = 'content';
    public const PROPERTY_FRAGMENT_SUMMARY = 'summary';
    public const PROPERTY_FRAGMENT_DEPENDENCIES = 'dependencies';

    public const QUERY_SEARCH_NO_LINKS = 'docs-no-links';
    public const QUERY_SEARCH_SOURCES_ONLY = 'docs-sources-only';



    protected string $src;
    protected int $srcLength;
    protected UserResource $resource;

    public function __construct() {
        parent::__construct();

        $this->resource = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOCS);

        $this->src = realpath(RouteChasmEnvironment::SRC);
        $this->srcLength = strlen($this->src);
    }



    /**
     * @param string $query
     * @param int $maxEntries
     * @param bool $sourcesOnly
     * @return array<Path>
     */
    protected function searchFiles(string $query, int $maxEntries = PHP_INT_MAX, bool $sourcesOnly = false): array {
        $files = [];
        $iterator = new DirectoryIterator($this->src);

        $this->searchFilesRecursive(
            $files,
            $iterator,
            $query,
            $maxEntries,
            $sourcesOnly
        );

        return $files;
    }

    protected function searchFilesRecursive(
        array &$results,
        DirectoryIterator $iterator,
        string $query,
        int $maxEntries = PHP_INT_MAX,
        bool $sourcesOnly = false
    ): void {
        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                $sub = new DirectoryIterator($item->getPathname());
                $this->searchFilesRecursive(
                    $results, $sub, $query, $maxEntries, $sourcesOnly
                );

                if ($sourcesOnly) {
                    continue;
                }

                if (count($results) >= $maxEntries) {
                    break;
                }
            }

            if (!str_contains(strtolower($item->getFilename()), $query)) {
                continue;
            }

            $results[] = $item->getRealPath();

            if (count($results) >= $maxEntries) {
                break;
            }
        }
    }

    public function createSearchUrl(): Url {
        return $this->createUrl(Path::from('search'));
    }

    public function requestFragmentGeneration(Client $client, string $file): array {
        $request = $client->createRequest();

        $schema = new Schema(
            'fragment_generation',
            (new ObjectSchema())
                ->add(self::PROPERTY_FRAGMENT_SUMMARY, new StringSchema())
                ->add(self::PROPERTY_FRAGMENT_DEPENDENCIES, new ArraySchema(new StringSchema()))
                ->setRequired([self::PROPERTY_FRAGMENT_SUMMARY, self::PROPERTY_FRAGMENT_DEPENDENCIES])
        );

        $request
            ->setSchema($schema)
            ->add(new FragmentGeneration(InputMessage::ROLE_SYSTEM, $file))
            ->add(new FragmentGeneration(InputMessage::ROLE_USER, $file));

        return $client->parseResponse(
            $client->chat($request)
        );
    }

    /**
     * @param Client $client
     * @param Language $language
     * @param string $file
     * @param array<Fragment> $fragments
     * @return array
     */
    public function requestDocumentGeneration(Client $client, Language $language, string $file, array $fragments): array {
        $request = $client->createRequest();

        $schema = new Schema(
            'document_generation',
            (new ObjectSchema())
                ->add(self::PROPERTY_DOCUMENT_CONTENT, new StringSchema())
                ->setRequired([self::PROPERTY_DOCUMENT_CONTENT])
        );

        $request
            ->setSchema($schema)
            ->add(new DocumentGeneration(InputMessage::ROLE_SYSTEM, $language, $file, $fragments))
            ->add(new DocumentGeneration(InputMessage::ROLE_USER, $language, $file, $fragments));

        return $client->parseResponse(
            $client->chat($request)
        );
    }

    public function documentFragment(Client $client, string $file, ?array &$dependencies = null): ?Fragment {
        if (empty($fragmentResponse = $this->requestFragmentGeneration($client, $file))) {
            return null;
        }

        if (!isset($fragmentResponse[self::PROPERTY_FRAGMENT_SUMMARY])) {
            var_dump($fragmentResponse);
            App::getInstance()
                ->getResponse()
                ->sendStatus(HttpCode::SE_INTERNAL_SERVER_ERROR);
        }

        $fragment = new Fragment();

        $fragment->name = $file;
        $fragment->summary = $fragmentResponse[self::PROPERTY_FRAGMENT_SUMMARY];
        $fragment->save();

        $dependencies = $fragmentResponse[self::PROPERTY_FRAGMENT_DEPENDENCIES];

        return $fragment;
    }

    protected function createFragment(Client $client, string $file, mixed &$fragmentResponse = null): ?Fragment {
        $fragment = Fragment::fromName($file) ?? new Fragment();
        $fragment->name = $file;

        if (empty($fragmentResponse = $this->requestFragmentGeneration($client, $file))) {
            $fragment->summary = 'Route Chasm source file';
            $fragment->save();
            return null;
        }

        $fragment->summary = $fragmentResponse[self::PROPERTY_FRAGMENT_SUMMARY];
        $fragment->save();

        return $fragment;
    }

    protected function generateDocumentation(
        Document $document,
        Client $client,
        Language $language,
        string $file,
        array $fragments
    ): void {
        $documentResponse = $this->requestDocumentGeneration($client, $language, $file, $fragments);

        if (empty($documentResponse)) {
            $document->getContent($language)
                ->write('# '. Files::split($file)[0]);
        } else {
            $document->getContent($language)
                ->write($documentResponse[self::PROPERTY_DOCUMENT_CONTENT]);
        }
    }

    public function document(string $file, Language $language): ?Document {
        $client = OpenAi::fromEnv();

        $fragments = [];

        $this->createFragment($client, $file, $fragmentResponse);

        foreach ($fragmentResponse['dependencies'] as $dependency) {
            $path = Path::merge($this->src, Path::from($dependency .'.php', separator: '\\'));
            $filePath = $path->toString(DIRECTORY_SEPARATOR, false);
            if (!file_exists($filePath)) {
                continue;
            }

            $filePath = realpath($filePath);
            if (!is_null($fragment = Fragment::fromName($filePath))) {
                $fragments[] = $fragment;
                continue;
            }

            $fragments[] = $this->documentFragment($client, $filePath);
        }

        $document = Document::fromFile($file, true);
        $this->generateDocumentation(
            $document, $client, $language, $file, $fragments
        );

        $document->clearFragments();
        $document->addFragments($fragments);

        return $document;
    }

    public function trimSrc(string $entryPath): string {
        return substr($entryPath, $this->srcLength);
    }

    public function createEntryUrl(string $entryPath): Url {
        return $this->createUrl(Path::from(
            $this->trimSrc($entryPath),
            separator: DIRECTORY_SEPARATOR
        ));
    }

    /**
     * @param array<Fragment> $fragments
     * @return array
     */
    protected function createRelated(array $fragments): array {
        $ret = [];

        foreach ($fragments as $fragment) {
            $name = Path::from(
                substr($fragment->name, $this->srcLength),
                separator: DIRECTORY_SEPARATOR
            );

            $ret[] = [
                'link' => $this->createUrl($name),
                'label' => Files::removeExtension($name->toString('\\'))
            ];
        }

        return $ret;
    }

    protected function createBreadCrumbs(Path $path, ?string $delimitor = RouteChasmEnvironment::BREAD_CRUMBS_DELIMITOR): BreadCrumbs {
        if ($path->isEmpty()) {
            return BreadCrumbs::from([], $delimitor);
        }

        $breadCrumbs = [
            $this->createUrl()->toString() => Icon::home()
        ];

        $segments = $path->getSegments();
        $last = array_pop($segments);

        for ($i = 0; $i < count($segments); $i++) {
            $entry = implode(DIRECTORY_SEPARATOR, array_slice($segments, 0, $i + 1));
            $url = $this->createEntryUrl(
                Path::joinArray([$this->src, $entry], DIRECTORY_SEPARATOR)
            );

            $breadCrumbs[$url->toString()] = $segments[$i];
        }

        $breadCrumbs[] = $last;
        return BreadCrumbs::from($breadCrumbs, $delimitor);
    }

    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/search',
            new Block($this->resource, $read = Privilege::fromName(Privilege::READ)),
            function (Request $request, Response $response) {
                $url = $request->getUrl()
                    ->getQuery();

                $query = $url->get(RouteChasmEnvironment::QUERY_SEARCH);

                if (empty($query)) {
                    $response->json([]);
                }

                $isLink = !$url->exists(self::QUERY_SEARCH_NO_LINKS);

                $maxEntries = Setting::fromName(
                    RouteChasmEnvironment::SETTING_DROPDOWN_MAX_ENTRIES,
                    true,
                    RouteChasmEnvironment::SEARCH_DROPDOWN_MAX_ENTRIES,
                    [PROPERTY_EDITABLE => true]
                )->toInt();

                $results = array_map(
                    function (string $x) use ($isLink) {
                        $title = basename($x);
                        $meta = is_dir($x)
                            ? 'Namespace'
                            : 'Source file';

                        if ($isLink) {
                            return new SearchResult(
                                $title,
                                $this->createUrl(
                                    Path::from(substr($x, $this->srcLength),
                                        separator: DIRECTORY_SEPARATOR
                                    )
                                ),
                                $meta
                            );
                        }

                        return new SearchResult($title, $x, $meta, false);
                    },
                    $this->searchFiles(strtolower($query), $maxEntries, $url->exists(self::QUERY_SEARCH_SOURCES_ONLY)),
                );

                $response->renderRoot(new SearchResults($results));
            }
        );

        $router->use('/**',
            Http::get(
                new Block($this->resource, $read),
                function (Request $request, Response $response) {
                    $file = Path::merge($this->src, $request->getRemainingPath())
                        ->toString(prependSlash: false);

                    if (!str_starts_with($file, $this->src) || !file_exists($file)) {
                        $response->sendStatus(HttpCode::CE_NOT_FOUND);
                    }

                    if (!$request->getUrl()->getQuery()->exists('content')) {
                        $breadCrumbs = $this->createBreadCrumbs($request->getRemainingPath());
                        $page = new DocumentPage(basename($file), $this, $breadCrumbs);

                        if (is_dir($file)) {
                            $page->setDirectory($file);
                        }

                        $response->renderRoot($page->setUserResource($this->resource));
                    }

                    $language = $request->getLanguage();
                    $file = realpath($file);

                    $doc = Document::fromFile($file);
                    if (!is_null($doc) && !$doc->needsUpdate()) {
                        if ($doc->getContent($language)->exists()) {
                            $response->json([
                                'content' => $doc->getContent($language)->read(),
                                'related' => $this->createRelated($doc->getFragments())
                            ]);
                        }

                        $this->generateDocumentation(
                            $doc, OpenAi::fromEnv(), $language, $file, $doc->getFragments()
                        );
                        $doc->updateFileInfo(true);

                        $response->json([
                            'content' => $doc->getContent($language)->read(),
                            'related' => $this->createRelated($doc->getFragments())
                        ]);
                    }

                    if (is_null($doc = $this->document($file, $language))) {
                        $response->json([
                            'content' => '# Failed to generate documentation for: '. $request->getRemainingPath(),
                            'related' => [],
                        ]);
                    }

                    $response->json([
                        'content' => $doc->getContent($language)->read(),
                        'related' => $this->createRelated($doc->getFragments())
                    ]);
                }
            )->setCheckIsLastAction(false),

            Http::post(
                new Block($this->resource, Privilege::fromName(Privilege::UPDATE)),
                function (Request $request, Response $response) {
                    $file = Path::merge($this->src, $request->getRemainingPath())
                        ->toString(prependSlash: false);

                    if (!str_starts_with($file, $this->src) || !file_exists($file)) {
                        $response->sendStatus(HttpCode::CE_NOT_FOUND);
                    }

                    $file = realpath($file);
                    $language = $request->getLanguage();

                    if (is_null($doc = Document::fromFile($file))) {
                        var_dump($doc);
                        $response->sendStatus(HttpCode::SE_INTERNAL_SERVER_ERROR);
                    }

                    $doc->getContent($language)
                        ->write($request->getBody()->getStrict('content'));

                    $response->sendStatus(HttpCode::S_OK);
                }
            )->setCheckIsLastAction(false),

            Http::put(
                new Block($this->resource, Privilege::fromName(Privilege::UPDATE)),
                function (Request $request, Response $response) {
                    $file = Path::merge($this->src, $request->getRemainingPath())
                        ->toString(prependSlash: false);

                    if (!str_starts_with($file, $this->src) || !file_exists($file)) {
                        $response->sendStatus(HttpCode::CE_NOT_FOUND);
                    }

                    $file = realpath($file);
                    $language = $request->getLanguage();

                    if (is_null($doc = $this->document($file, $language))) {
                        $response->sendStatus(HttpCode::SE_SERVICE_UNAVAILABLE);
                    }

                    $response->sendStatus(HttpCode::S_OK);
                }
            )->setCheckIsLastAction(false),
        );
    }
}
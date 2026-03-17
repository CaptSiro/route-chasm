<?php

namespace components\docs;

use components\ai\AiRequest;
use components\ai\DocumentGeneration\DocumentGeneration;
use components\ai\FragmentGeneration\FragmentGeneration;
use components\ai\InputMessage;
use components\ai\Schema\ArraySchema;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\Search\SearchResult;
use components\core\Search\SearchResults;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\url\Url;
use core\utils\Files;
use FilesystemIterator;
use models\core\Language\Language;
use models\core\Setting\Setting;
use models\docs\Document;
use models\docs\Fragment;
use modules\ai\OpenAi;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Docs extends Router {
    use Singleton;

    public const SETTING_NAME_DROPDOWN_MAX_ENTRIES = 'route-chasm-docs:search_dropdown_max_entries';

    public const PROPERTY_DOCUMENT_CONTENT = 'content';
    public const PROPERTY_FRAGMENT_SUMMARY = 'summary';
    public const PROPERTY_FRAGMENT_DEPENDENCIES = 'dependencies';



    /**
     * @param string $query
     * @param int $maxEntries
     * @return array<Path>
     */
    protected function searchFiles(string $query, int $maxEntries = PHP_INT_MAX): array {
        $src = realpath(RouteChasmEnvironment::SRC);
        $srcLength = strlen($src);
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if (!str_contains(strtolower($item->getFilename()), $query)) {
                continue;
            }

            $files[] = Path::from(substr($item->getRealPath(), $srcLength), separator: DIRECTORY_SEPARATOR);
            if (count($files) >= $maxEntries) {
                break;
            }
        }

        return $files;
    }

    public function createSearchUrl(): Url {
        return $this->createUrl(Path::from('search'));
    }

    public function requestFragmentGeneration(OpenAi $client, string $file): array {
        $request = new AiRequest('gpt-4o-mini');

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
     * @param OpenAi $client
     * @param Language $language
     * @param string $file
     * @param array<Fragment> $fragments
     * @return array
     */
    public function requestDocumentGeneration(OpenAi $client, Language $language, string $file, array $fragments): array {
        $request = new AiRequest('gpt-4o-mini');

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

    public function documentFragment(OpenAi $client, string $file, ?array &$dependencies = null): ?Fragment {
        if (empty($fragmentResponse = $this->requestFragmentGeneration($client, $file))) {
            return null;
        }

        if (!isset($fragmentResponse[self::PROPERTY_FRAGMENT_SUMMARY])) {
            var_dump($fragmentResponse);
            exit;
        }

        $fragment = new Fragment();

        $fragment->name = $file;
        $fragment->summary = $fragmentResponse[self::PROPERTY_FRAGMENT_SUMMARY];
        $fragment->save();

        $dependencies = $fragmentResponse[self::PROPERTY_FRAGMENT_DEPENDENCIES];

        return $fragment;
    }

    protected function createFragment(OpenAi $client, string $file, mixed &$fragmentResponse = null): ?Fragment {
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
        OpenAi $client,
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
        $src = realpath(RouteChasmEnvironment::SRC);

        $this->createFragment($client, $file, $fragmentResponse);

        foreach ($fragmentResponse['dependencies'] as $dependency) {
            $path = Path::merge($src, Path::from($dependency .'.php', separator: '\\'));
            $filePath = $path->toString(DIRECTORY_SEPARATOR, false);
            if (!file_exists($filePath)) {
                continue;
            }

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

    public function createEntryUrl(string $entryPath): Url {
        $srcLength = strlen(realpath(RouteChasmEnvironment::SRC));

        return $this->createUrl(Path::from(
            substr($entryPath, $srcLength),
            separator: DIRECTORY_SEPARATOR
        ));
    }

    /**
     * @param array<Fragment> $fragments
     * @return array
     */
    protected function createRelated(array $fragments): array {
        $ret = [];
        $src = strlen(realpath(RouteChasmEnvironment::SRC));

        foreach ($fragments as $fragment) {
            $name = Path::from(
                substr($fragment->name, $src),
                separator: DIRECTORY_SEPARATOR
            );

            $ret[] = [
                'link' => $this->createUrl($name),
                'label' => Files::removeExtension($name->toString('\\'))
            ];
        }

        return $ret;
    }

    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/search', function (Request $request, Response $response) {
            $query = $request->getUrl()
                ->getQuery()
                ->get(RouteChasmEnvironment::QUERY_SEARCH);

            if (empty($query)) {
                $response->json([]);
            }

            $maxEntries = Setting::fromName(
                self::SETTING_NAME_DROPDOWN_MAX_ENTRIES,
                true,
                RouteChasmEnvironment::SEARCH_DROPDOWN_MAX_ENTRIES,
                [PROPERTY_EDITABLE => true]
            )->toInt();

            $results = array_map(
                fn(Path $x) => new SearchResult($x->last(), $this->createUrl($x)),
                $this->searchFiles(strtolower($query), $maxEntries),
            );

            $response->renderRoot(new SearchResults($results));
        });

        $router->use('/**', function (Request $request, Response $response) {
            $src = realpath(RouteChasmEnvironment::SRC);
            $file = Path::merge($src, $request->getRemainingPath());
            $filePath = $file->toString(prependSlash: false);

            if (!$request->getUrl()->getQuery()->exists('content')) {
                if (is_dir($filePath)) {
                    $response->renderRoot(new DocumentPage($this, $filePath));
                }

                $response->renderRoot(new DocumentPage($this));
            }

            $language = $request->getLanguage();

            if (!str_starts_with($filePath, $src) || !file_exists($filePath)) {
                $response->sendStatus(HttpCode::CE_NOT_FOUND);
            }

            $doc = Document::fromFile($filePath);
            if (!is_null($doc) && !$doc->needsUpdate()) {
                if ($doc->getContent($language)->exists()) {
                    $response->json([
                        'content' => $doc->getContent($language)->read(),
                        'related' => $this->createRelated($doc->getFragments())
                    ]);
                }

                $this->generateDocumentation(
                    $doc, OpenAi::fromEnv(), $language, $filePath, $doc->getFragments()
                );

                $response->json([
                    'content' => $doc->getContent($language)->read(),
                    'related' => $this->createRelated($doc->getFragments())
                ]);
            }

            if (is_null($doc = $this->document($filePath, $language))) {
                $response->json([
                    'content' => '# Failed to generate documentation for: '. $request->getRemainingPath(),
                    'related' => [],
                ]);
            }

            $response->json([
                'content' => $doc->getContent($language)->read(),
                'related' => $this->createRelated($doc->getFragments())
            ]);
        });
    }
}
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
use core\App;
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

    public function createFragmentRequest(string $file): AiRequest {
        $request = new AiRequest('gpt-4o-mini');

        $schema = new Schema(
            'fragment_generation',
            (new ObjectSchema())
                ->add('summary', new StringSchema())
                ->add('dependencies', new ArraySchema(new StringSchema()))
                ->setRequired(['summary', 'dependencies'])
        );

        $request
            ->setSchema($schema)
            ->add(new FragmentGeneration(InputMessage::ROLE_SYSTEM, $file))
            ->add(new FragmentGeneration(InputMessage::ROLE_USER, $file));

        return $request;
    }

    /**
     * @param string $file
     * @param array<Fragment> $fragments
     * @return AiRequest
     */
    public function createDocumentRequest(string $file, array $fragments): AiRequest {
        $request = new AiRequest('gpt-4o-mini');

        $schema = new Schema(
            'document_generation',
            (new ObjectSchema())
                ->add('content', new StringSchema())
                ->setRequired(['content'])
        );

        $request
            ->setSchema($schema)
            ->add(new DocumentGeneration(InputMessage::ROLE_SYSTEM, $file, $fragments))
            ->add(new DocumentGeneration(InputMessage::ROLE_USER, $file, $fragments));

        return $request;
    }

    public function documentFragment(OpenAi $client, string $file, ?array &$dependencies = null): ?Fragment {
        $fragmentRequest = $this->createFragmentRequest($file);
        $result = $client->chat($fragmentRequest);
        $fragmentResponse = $client->parseResponse($result);

        if (is_null($fragmentResponse)) {
            return null;
        }

        if (!isset($fragmentResponse['summary'])) {
            var_dump($fragmentResponse);
            exit;
        }

        $fragment = new Fragment();

        $fragment->name = $file;
        $fragment->summary = $fragmentResponse['summary'];
        $fragment->save();

        $dependencies = $fragmentResponse['dependencies'];

        return $fragment;
    }

    public function document(string $file): ?Document {
        $client = OpenAi::fromEnv();

        $fragments = [];
        $src = realpath(RouteChasmEnvironment::SRC);

        $f = Fragment::fromName($file) ?? new Fragment();
        $f->name = $file;

        $fragmentResponse = $client->parseResponse($client->chat($this->createFragmentRequest($file)));

        if (is_null($fragmentResponse)) {
            $f->summary = 'Route Chasm source file';
            $f->save();
            return null;
        }

        $f->summary = $fragmentResponse['summary'];
        $f->save();

        foreach ($fragmentResponse['dependencies'] as $dependency) {
            $f = Path::merge($src, Path::from($dependency .'.php', separator: '\\'));
            $filePath = $f->toString(DIRECTORY_SEPARATOR, false);
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
        $documentResponse = $client->parseResponse($client->chat($this->createDocumentRequest($file, $fragments)));
        if (is_null($documentResponse)) {
            $document->getContent()
                ->write('# '. Files::split($file)[0]);
        } else {
            $document->getContent()
                ->write($documentResponse['content']);
        }

        $document->clearFragments();
        $document->addFragments($fragments);

        return $document;
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
            if (!$request->getUrl()->getQuery()->exists('content')) {
                $response->renderRoot(new DocumentPage());
            }

            $src = realpath(RouteChasmEnvironment::SRC);
            $file = Path::merge($src, $request->getRemainingPath());
            $filePath = $file->toString(prependSlash: false);

            if (!str_starts_with($filePath, $src) || !file_exists($filePath)) {
                $response->sendStatus(HttpCode::CE_NOT_FOUND);
            }

            $doc = Document::fromFile($filePath);
            if (!is_null($doc) && !$doc->needsUpdate()) {
                $response->json([
                    'content' => $doc->getContent()->read(),
                    'related' => $this->createRelated($doc->getFragments())
                ]);
            }

            if (is_null($doc = $this->document($filePath))) {
                $response->json([
                    'content' => '# '. $request->getRemainingPath(),
                    'related' => [],
                ]);
            }

            $response->json([
                'content' => $doc->getContent()->read(),
                'related' => $this->createRelated($doc->getFragments())
            ]);
        });
    }
}
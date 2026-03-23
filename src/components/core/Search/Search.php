<?php

namespace components\core\Search;

use components\core\Html\Html;
use components\pages\Listing\ListingTemplate;
use core\communication\Request;
use core\communication\Response;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\url\Url;
use core\view\StringRenderer;
use models\core\Page\Page;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Search extends Router {
    use Singleton, LexiconUnit;

    public const LEXICON_GROUP = 'search';



    public static function createApi(): string {
        $search = static::getInstance();
        return Html::wrapUnsafe(
            'script',
            json_encode([
                'searchFullTextUrl' => $search->createFullTextUrl(),
                'searchQuery' => RouteChasmEnvironment::QUERY_SEARCH
            ]),
            [
                'type' => 'application/json',
                'id' => 'api-search'
            ]
        );
    }



    public function __construct() {
        parent::__construct();
        $this->setLexiconGroup(static::LEXICON_GROUP);
    }



    protected function createFullTextUrl(): Url {
        return $this->createUrl();
    }

    protected function createResultsUrl(string $query): Url {
        $ret = $this->createUrl(Path::from('results'));
        $ret->setQueryArgument(RouteChasmEnvironment::QUERY_SEARCH, $query);
        return $ret;
    }

    protected function onBind(RouteNode $bindingPoint): void {
        $router = $bindingPoint->getRouter();

        $router->use('/', function (Request $request, Response $response) {
            $query = $request->getUrl()
                ->getQuery()
                ->getStrict(RouteChasmEnvironment::QUERY_SEARCH);

            $maxEntries = Setting::fromName(
                RouteChasmEnvironment::SETTING_DROPDOWN_MAX_ENTRIES,
                true,
                RouteChasmEnvironment::SEARCH_DROPDOWN_MAX_ENTRIES,
                [PROPERTY_EDITABLE => true]
            )->toInt();

            $language = $request->getLanguage();
            $sql = Page::searchFullTextQuery($query, $language->id)
                ->limit($maxEntries);

            $results = array_map(
                fn(Page $x) => $x->getTemplate()->buildSearchResult($x, $language),
                Page::getDescription()
                    ->getFactory()
                    ->allExecute($sql)
            );

            $viewAll = count($results) >= $maxEntries
                ? new StringRenderer(Html::wrapUnsafe(
                    'a',
                    $this->tr('View all results...'),
                    ['href' => $this->createResultsUrl($query)]
                ))
                : null;

            $response->renderRoot(new SearchResults($results, $viewAll));
        });

        $router->use('/results', function (Request $request, Response $response) {
            $query = $request->getUrl()
                ->getQuery()
                ->getStrict(RouteChasmEnvironment::QUERY_SEARCH);

            $portionSize = Setting::fromName(
                ListingTemplate::NAME_PORTION_SIZE,
                true,
                RouteChasmEnvironment::LISTING_PORTION_SIZE,
                [PROPERTY_EDITABLE => true]
            );

            $response->renderRoot(new SearchResultsListing(
                $query,
                $portionSize->toInt()
            ));
        });
    }
}
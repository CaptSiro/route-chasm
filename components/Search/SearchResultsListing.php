<?php

namespace components\Search;

use components\layout\Pagination\Pagination;
use components\layout\Pagination\PaginationControl;
use components\layout\Pagination\PaginationFactory;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Controller;
use core\view\Html;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use core\view\View;
use models\Page\Page;

class SearchResultsListing extends Controller {
    use LexiconUnit;

    public const LEXICON_GROUP = Search::LEXICON_GROUP;



    protected PaginationFactory $factory;

    public function __construct(
        protected string $query,
        int $portionSize = RouteChasmEnvironment::LISTING_PORTION_SIZE,
        Pagination&View $pagination = new PaginationControl(),
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $q = Html::escape($this->query);
        $this->setTitle($this->trt('Search results for: {}', $q));

        $this->factory = new PaginationFactory(
            new SearchResultsListingFactoryBehavior($this->query, $pagination),
            Page::getDescription(),
            $portionSize
        );
    }
}
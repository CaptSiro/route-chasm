<?php

namespace components\Search;

use components\layout\Pagination\Pagination;
use components\layout\Pagination\PaginationControl;
use components\layout\Pagination\PaginationFactory;
use components\layout\WebPage\WebPage;
use core\RouteChasmEnvironment;
use core\view\ContainerContent;
use core\view\Html;
use core\view\View;
use models\Page\Page;

class SearchResultsListing extends ContainerContent {
    public const LEXICON_GROUP = Search::LEXICON_GROUP;



    protected PaginationFactory $factory;
    protected WebPage $webPage;

    public function __construct(
        protected string $query,
        int $portionSize = RouteChasmEnvironment::LISTING_PORTION_SIZE,
        Pagination&View $pagination = new PaginationControl()
    ) {
        parent::__construct($this->webPage = new WebPage());
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $q = Html::escape($this->query);
        $this->webPage->getHead()
            ->setTitle($this->tr('Search results for') .": '$q'");

        $this->factory = new PaginationFactory(
            new SearchResultsListingFactoryBehavior($this->query, $pagination),
            Page::getDescription(),
            $portionSize
        );
    }
}
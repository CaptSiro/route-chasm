<?php

namespace components\core\Search;

use components\core\Html\Html;
use components\core\Pagination\Pagination;
use components\core\Pagination\PaginationControl;
use components\core\Pagination\PaginationFactory;
use components\core\WebPage\WebPage;
use core\RouteChasmEnvironment;
use core\view\ContainerContent;
use core\view\View;
use models\core\Page\Page;

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
<?php

namespace components\Search;

use components\layout\Pagination\DefaultPaginationFactoryBehavior;
use components\layout\Pagination\Pagination;
use components\layout\Pagination\PaginationFactoryBehavior;
use core\App;
use core\database\sql\query\SelectQuery;
use core\view\View;
use models\Page\Page;

class SearchResultsListingFactoryBehavior implements PaginationFactoryBehavior {
    use DefaultPaginationFactoryBehavior;



    public function __construct(
        protected string $query,
        protected Pagination&View $pagination
    ) {}



    public function getSelectQuery(): SelectQuery {
        $language = App::getInstance()
            ->getRequest()
            ->getLanguage();

        return Page::searchFullTextQuery($this->query, $language->id);
    }
}
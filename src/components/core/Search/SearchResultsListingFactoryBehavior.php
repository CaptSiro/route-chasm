<?php

namespace components\core\Search;

use components\core\Pagination\DefaultPaginationFactoryBehavior;
use components\core\Pagination\Pagination;
use components\core\Pagination\PaginationFactoryBehavior;
use core\App;
use core\database\sql\query\SelectQuery;
use core\view\View;
use models\core\Page\Page;

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
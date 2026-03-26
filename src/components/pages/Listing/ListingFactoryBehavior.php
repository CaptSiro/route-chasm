<?php

namespace components\pages\Listing;

use components\core\Pagination\Pagination;
use components\core\Pagination\PaginationFactoryBehavior;
use components\core\Pagination\DefaultPaginationFactoryBehavior;
use core\database\sql\query\SelectQuery;
use core\view\View;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use const models\extensions\Priority\COLUMN_PRIORITY;

class ListingFactoryBehavior implements PaginationFactoryBehavior {
    use DefaultPaginationFactoryBehavior;



    public function __construct(
        protected Page $page,
        protected Pagination&View $pagination
    ) {}



    public function getSelectQuery(): SelectQuery {
        $description = $this->page::getDescription();
        return $description->getFactory()
            ->allQuery()
            ->where(Page::childrenQuery($this->page->id))
            ->where(Page::isStatusQuery(PageStatus::ID_PUBLIC))
            ->where(Page::publishedQuery())
            ->order(COLUMN_PRIORITY);
    }
}
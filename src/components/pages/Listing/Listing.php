<?php

namespace components\pages\Listing;

use components\core\PaginationControl\Pagination;
use components\core\PaginationControl\PaginationControl;
use components\core\PaginationControl\Portion;
use components\core\PaginationControl\PortionUrlCreator;
use core\App;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\View;
use models\core\Page\PageLocalization;
use models\core\Page\Page;
use models\core\Page\PageStatus;

class Listing extends Component {
    use Portion;



    protected int $current;
    protected int $count;

    public function __construct(
        protected Page $page,
        protected PageLocalization $localization,
        protected int $portionSize = RouteChasmEnvironment::LISTING_PORTION_SIZE,
        protected Pagination&View $pagination = new PaginationControl(),
    ) {
        parent::__construct();
    }



    protected function getSelectQuery(): SelectQuery {
        $description = $this->page::getDescription();
        return $description->getFactory()
            ->allQuery()
            ->where(Page::childrenQuery($this->page->getId()))
            ->where(Page::isStatusQuery(PageStatus::ID_PUBLIC))
            ->where(Page::publishedQuery());
    }

    public function getCurrent(): int {
        if (isset($this->current)) {
            return $this->current;
        }

        return $this->current = $this->calculateCurrent(
            $this->portionSize,
            $this->getCount(),
            PortionUrlCreator::getPortion(App::getInstance()->getRequest())
        );
    }

    protected function getCount(): int {
        if (isset($this->count)) {
            return $this->count;
        }

        $factory = $this->page::getDescription()
            ->getFactory();

        return $this->count = $factory
            ->countExecute($this->getSelectQuery()
                ->clearProjection()
                ->projection($factory->countProjection()));
    }

    /**
     * @return array<Page>
     */
    public function getPages(): array {
        $description = $this->page::getDescription();

        return $description->getFactory()
            ->allExecute(
                $this->setQueryLimit(
                    $this->getSelectQuery(),
                    $this->getCurrent(),
                    $this->portionSize
                )
            );
    }

    public function getPagination(): View {
        $count = $this->getCount();

        return $this->pagination
            ->setCurrent($this->getCurrent())
            ->setMax($this->calculateMax($this->portionSize, $count))
            ->setUrlCreator(new PortionUrlCreator(
                App::getInstance()->getRequest()->getUrl(),
            ));
    }
}
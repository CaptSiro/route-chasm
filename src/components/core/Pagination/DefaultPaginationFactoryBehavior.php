<?php

namespace components\core\Pagination;

use core\App;

trait DefaultPaginationFactoryBehavior {
    protected PaginationFactory $paginationFactory;



    public function setPaginationFactory(PaginationFactory $factory): void {
        $this->paginationFactory = $factory;
    }

    public function getPortion(): int {
        return PortionUrlCreator::getPortion(App::getInstance()->getRequest());
    }

    public function createPagination(): Pagination {
        return $this->pagination
            ->setUrlCreator(new PortionUrlCreator(
                App::getInstance()->getRequest()->getUrl(),
            ));
    }
}
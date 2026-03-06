<?php

namespace components\core\Pagination;

use core\database\sql\query\SelectQuery;

interface PaginationFactoryBehavior {
    public function setPaginationFactory(PaginationFactory $factory): void;

    public function getSelectQuery(): SelectQuery;

    public function getPortion(): int;

    public function createPagination(): Pagination;
}
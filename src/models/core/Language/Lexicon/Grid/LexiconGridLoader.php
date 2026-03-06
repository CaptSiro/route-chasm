<?php

namespace models\core\Language\Lexicon\Grid;

use components\core\Pagination\Pagination;
use components\core\Pagination\PaginationControl;
use components\layout\Grid\Loader\ModelGridLoader;
use core\database\sql\ModelFactory;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;

class LexiconGridLoader extends ModelGridLoader {
    public function __construct(
        bool $paginate = true,
        int $portionSize = RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE,
        Pagination $pagination = new PaginationControl()
    ) {
        parent::__construct(
            LexiconGridRow::class,
            $paginate,
            $portionSize,
            $pagination
        );
    }



    protected function createSelectQuery(ModelFactory $factory): SelectQuery {
        return LexiconGridRow::phrasesQuery($factory->getDescription()->getConnection());
    }

    protected function getCount(ModelFactory $factory): int {
        return LexiconGridRow::phrasesCount();
    }
}
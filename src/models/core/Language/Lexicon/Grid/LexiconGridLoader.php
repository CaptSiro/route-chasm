<?php

namespace models\core\Language\Lexicon\Grid;

use components\layout\Grid\Loader\ModelGridLoader;
use core\database\sql\ModelFactory;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;

class LexiconGridLoader extends ModelGridLoader {
    public function __construct(
        bool $paginate = true,
        int $portionSize = RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE
    ) {
        parent::__construct(LexiconGridRow::class, $paginate, $portionSize);
    }



    protected function createSelectQuery(ModelFactory $factory): SelectQuery {
        return LexiconGridRow::phrasesQuery($factory->getDescription()->getConnection());
    }

    protected function getCount(ModelFactory $factory): int {
        return LexiconGridRow::phrasesCount();
    }
}
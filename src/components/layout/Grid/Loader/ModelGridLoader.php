<?php

namespace components\layout\Grid\Loader;

use components\core\PaginationControl\PaginationControl;
use components\core\Terminal\Terminal;
use components\layout\Grid\Grid;
use core\App;
use core\database\sql\ModelFactory;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;

class ModelGridLoader implements GridPortionLoader {
    use GridPortion;

    public function __construct(
        protected string $modelClass,
        protected bool $paginate = true,
        int $portionSize = RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE,
    ) {
        $this->setPortionSize($portionSize);
    }



    protected function createSelectQuery(ModelFactory $factory): SelectQuery {
        return $factory->allQuery();
    }

    protected function setLimit(SelectQuery $query, int $portion, int $portionSize): SelectQuery {
        return $query
            ->limit($portionSize)
            ->offset(($portion - 1) * $portionSize);
    }

    protected function getCount(ModelFactory $factory): int {
        return $factory->count();
    }

    public function load(Grid $context): array {
        $factory = ModelFactory::extract($this->modelClass);
        if (!$this->paginate) {
            return $factory->all();
        }

        $request = App::getInstance()->getRequest();
        $portionSize = $this->portionSize > 0
            ? $this->portionSize
            : RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE;

        $max = intval(ceil($this->getCount($factory) / $portionSize));
        if ($max === 1) {
            return $factory->all();
        }

        $portion = min(max(1, GridLoaderUrlCreator::getPortion($request)), $max);

        $context->setFooter(
            new PaginationControl(
                $portion,
                $max,
                new GridLoaderUrlCreator(
                    $request->getUrl(),
                    $context
                )
            )
        );

        return $factory->allExecute($this->setLimit(
            $this->createSelectQuery($factory),
            $portion,
            $portionSize
        ));
    }

}
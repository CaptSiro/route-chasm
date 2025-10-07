<?php

namespace components\layout\Grid\Loader;

use components\core\PaginationControl\Pagination;
use components\core\PaginationControl\PaginationControl;
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
        protected Pagination $pagination = new PaginationControl(),
    ) {
        $this->setPortionSize($portionSize);
    }



    public function setPagination(Pagination $pagination): void {
        $this->pagination = $pagination;
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
        $query = $this->createSelectQuery($factory);
        if (!$this->paginate) {
            return $factory->allExecute($query);
        }

        $request = App::getInstance()->getRequest();
        $portionSize = $this->portionSize > 0
            ? $this->portionSize
            : RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE;

        $max = intval(ceil($this->getCount($factory) / $portionSize));
        if ($max === 1) {
            return $factory->allExecute($query);
        }

        $portion = min(max(1, GridLoaderUrlCreator::getPortion($request)), $max);

        $context->setFooter(
            $this->pagination
                ->setCurrent($portion)
                ->setMax($max)
                ->setUrlCreator(new GridLoaderUrlCreator(
                    $request->getUrl(),
                    $context
                ))
        );

        return $factory->allExecute($this->setLimit(
            $query,
            $portion,
            $portionSize
        ));
    }

}
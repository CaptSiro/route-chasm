<?php

namespace components\layout\Grid\Loader;

use components\core\Pagination\Pagination;
use components\core\Pagination\PaginationControl;
use components\core\Pagination\PortionUrlCreator;
use components\layout\Grid\GridLayout;
use core\App;
use core\database\sql\ModelFactory;
use core\database\sql\query\SelectQuery;
use core\RouteChasmEnvironment;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class ModelGridLoader implements GridPortionLoader {
    use GridPortion;



    public const NAME_PORTION_SIZE = 'route-chasm-core:number_of_model_rows_in_grid';

    public static function getPortionSizeSetting(): int {
        return Setting::fromName(
            self::NAME_PORTION_SIZE,
            true,
            RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE,
            [PROPERTY_EDITABLE => true]
        )->toInt();
    }



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

    protected function getCount(ModelFactory $factory): int {
        return $factory->count();
    }

    public function count(): int {
        return $this->getCount(ModelFactory::extract($this->modelClass));
    }

    public function load(GridLayout $context): array {
        $factory = ModelFactory::extract($this->modelClass);
        $query = $this->createSelectQuery($factory);
        if (!$this->paginate) {
            return $factory->allExecute($query);
        }

        $request = App::getInstance()->getRequest();
        $portionSize = $this->portionSize > 0
            ? $this->portionSize
            : RouteChasmEnvironment::GRID_DEFAULT_PORTION_SIZE;

        $count = $this->getCount($factory);
        $max = $this->calculateMax($portionSize, $count);
        if ($max === 1) {
            return $factory->allExecute($query);
        }

        $current = $this->calculateCurrent(
            $portionSize,
            $count,
            PortionUrlCreator::getPortion($request)
        );

        $context->setFooter(
            $this->pagination
                ->setCurrent($current)
                ->setMax($max)
                ->setUrlCreator(new GridLoaderUrlCreator(
                    $request->getUrl(),
                    $context
                ))
        );

        return $factory->allExecute($this->setQueryLimit(
            $query,
            $current,
            $portionSize
        ));
    }

}
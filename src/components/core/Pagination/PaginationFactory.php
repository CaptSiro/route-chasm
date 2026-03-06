<?php

namespace components\core\Pagination;

use core\database\sql\ModelDescription;
use core\view\View;

/**
 * @template T
 */
class PaginationFactory {
    use Portion;

    protected int $current;
    protected int $count;



    public function __construct(
        protected PaginationFactoryBehavior $behavior,
        protected ModelDescription $description,
        protected int $portionSize,
    ) {
        $this->behavior->setPaginationFactory($this);
    }



    public function getCurrent(): int {
        if (isset($this->current)) {
            return $this->current;
        }

        return $this->current = $this->calculateCurrent(
            $this->portionSize,
            $this->getCount(),
            $this->behavior->getPortion()
        );
    }

    protected function getCount(): int {
        if (isset($this->count)) {
            return $this->count;
        }

        $factory = $this->description->getFactory();

        return $this->count = $factory
            ->countExecute($this->behavior->getSelectQuery()
                ->clearProjection()
                ->projection($factory->countProjection()));
    }

    /**
     * @return array<T>
     */
    public function getModels(): array {
        return $this->description->getFactory()
            ->allExecute(
                $this->setQueryLimit(
                    $this->behavior->getSelectQuery(),
                    $this->getCurrent(),
                    $this->portionSize
                )
            );
    }

    public function createPagination(): View {
        $count = $this->getCount();

        return $this->behavior->createPagination()
            ->setCurrent($this->getCurrent())
            ->setMax($this->calculateMax($this->portionSize, $count));
    }
}
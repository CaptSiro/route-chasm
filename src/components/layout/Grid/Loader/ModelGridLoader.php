<?php

namespace components\layout\Grid\Loader;

use components\layout\Grid\Grid;
use core\database\sql\ModelFactory;

class ModelGridLoader implements GridLoader {
    public function __construct(
        protected string $modelClass
    ) {}

    public function load(Grid $context): array {
        return ModelFactory::extract($this->modelClass)
            ->all();
    }
}
<?php

namespace models\extensions\Name;

use components\forms\description\SelectValues;

class NameValues implements SelectValues {
    public function __construct(
        protected string $modelClass
    ) {}

    public function getValues(): array {
        return call_user_func_array([$this->modelClass, 'createOptions'], []);
    }
}
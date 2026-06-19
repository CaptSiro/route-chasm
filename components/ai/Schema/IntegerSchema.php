<?php

namespace components\ai\Schema;

use core\view\JsonStructure;

class IntegerSchema extends JsonStructure {
    use Description, Nullable;

    public function __construct() {
        parent::__construct([
            'type' => 'integer',
        ]);
    }
}
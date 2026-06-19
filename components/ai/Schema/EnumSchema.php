<?php

namespace components\ai\Schema;

use core\view\JsonStructure;

class EnumSchema extends JsonStructure {
    use Description, Nullable;

    /**
     * @param array<string> $values
     */
    public function __construct(array $values) {
        parent::__construct([
            'type' => 'string',
            'enum' => $values
        ]);
    }
}
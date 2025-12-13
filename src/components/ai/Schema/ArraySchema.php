<?php

namespace components\ai\Schema;

use core\view\JsonStructure;
use JsonSerializable;

class ArraySchema extends JsonStructure {
    use Description, Nullable;

    public function __construct(JsonSerializable $itemSchema) {
        parent::__construct([
            'type' => 'array',
            'items' => $itemSchema
        ]);
    }



    public function setMinItems(int $value): static {
        $this->set('minItems', $value);
        return $this;
    }

    public function setMaxItems(int $value): static {
        $this->set('maxItems', $value);
        return $this;
    }
}
<?php

namespace components\ai\Schema;

use core\view\JsonStructure;

class NumberSchema extends JsonStructure {
    use Description, Nullable;



    public function __construct() {
        parent::__construct([
            'type' => 'number',
        ]);
    }



    public function setMultipleOf(int|float $value): static {
        $this->set('multipleOf', $value);
        return $this;
    }

    public function setMaximum(int|float $value): static {
        $this->set('maximum', $value);
        return $this;
    }

    public function setMinimum(int|float $value): static {
        $this->set('minimum', $value);
        return $this;
    }

    public function setExclusiveMaximum(int|float $value): static {
        $this->set('exclusiveMaximum', $value);
        return $this;
    }

    public function setExclusiveMinimum(int|float $value): static {
        $this->set('ExclusiveMinimum', $value);
        return $this;
    }
}
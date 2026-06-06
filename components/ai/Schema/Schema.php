<?php

namespace components\ai\Schema;

use core\view\JsonStructure;

class Schema extends JsonStructure {
    use Description;



    public function __construct(string $name, mixed $schema) {
        parent::__construct([
            'type' => 'json_schema',
            'name' => $name,
            'schema' => $schema,
            'strict' => true
        ]);
    }



    public function setIsStrict(bool $isStrict): static {
        $this->set('strict', $isStrict);
        return $this;
    }

    public function toFormat(): array {
        return ['format' => $this];
    }
}
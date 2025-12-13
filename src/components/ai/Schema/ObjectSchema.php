<?php

namespace components\ai\Schema;

use core\view\JsonStructure;
use JsonSerializable;

class ObjectSchema extends JsonStructure {
    use Description, Nullable;



    protected array $properties;

    public function __construct() {
        $this->properties = [];
        parent::__construct([
            'type' => 'object',
        ]);

        $this->setRef('properties', $this->properties);
        $this->setHasAdditionalProperties(false);
    }



    public function add(string $property, JsonSerializable $schema): static {
        $this->properties[$property] = $schema;
        return $this;
    }

    public function setHasAdditionalProperties(bool $hasAdditionalProperties): static {
        $this->set('additionalProperties', $hasAdditionalProperties);
        return $this;
    }

    /**
     * @param array<string> $required
     * @return $this
     */
    public function setRequired(array $required): static {
        $this->set('required', $required);
        return $this;
    }
}
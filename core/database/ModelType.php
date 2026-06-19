<?php

namespace core\database;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ModelType {
    /**
     * @param string $type kebab-case model (entity) type name used for string identifiers: model-type_sub-type#ID
     */
    public function __construct(
        protected string $type
    ) {}



    public function getType(): string {
        return $this->type;
    }
}
<?php

namespace components\layout\Grid\description;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GridColumn {
    public function __construct(
        public ?string $label = null,
        public string $template = '1fr'
    ) {}

    public function bindProperty(ReflectionProperty $property): void {
        $this->label ??= ucfirst($property->getName());
    }
}
<?php

namespace modules\forms\description;

use ReflectionProperty;

trait BindProperty {
    protected string $name;

    public function bindProperty(ReflectionProperty $property): void {
        $this->label ??= ucfirst($property->getName());
        $this->name = $property->getName();
    }
}
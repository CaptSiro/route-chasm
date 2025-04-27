<?php

namespace modules\forms\description;

use core\database_v3\sql\Column;
use core\database_v3\sql\ModelDescription;
use ReflectionProperty;

trait BindProperty {
    protected string $name;

    public function bindProperty(ReflectionProperty $property): void {
        $this->label ??= ucfirst($property->getName());
        $this->name = $property->getName();
    }
}
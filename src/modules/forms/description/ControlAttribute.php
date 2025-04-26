<?php

namespace modules\forms\description;

use modules\forms\controls\Control;
use ReflectionProperty;

interface ControlAttribute {
    public function getLabel(): string;

    public function bindProperty(ReflectionProperty $property): void;

    public function getControl(): Control;
}
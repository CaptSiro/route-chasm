<?php

namespace components\forms\description;

use components\forms\controls\Control;
use ReflectionProperty;

interface ControlAttribute {
    public function isFirst(): bool;

    public function getLabel(): string;

    public function bindProperty(ReflectionProperty $property): void;

    public function getControl(): Control;
}
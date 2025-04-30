<?php

namespace core\forms\description;

use core\forms\controls\Control;
use ReflectionProperty;

interface ControlAttribute {
    public function getLabel(): string;

    public function bindProperty(ReflectionProperty $property): void;

    public function getControl(): Control;
}
<?php

namespace core\mounts;

use core\route\Path;
use core\route\Route;

class ParametricMount implements Mount {
    use MountingPoint;



    public function __construct(
        protected string $alias,
        protected array $parameters
    ) {}



    public function getAlias(): string {
        return $this->alias;
    }

    public function transform(Route $route): Path {
        return $route->toPath($this->parameters);
    }
}
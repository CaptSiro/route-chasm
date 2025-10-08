<?php

namespace core\navigation\mounts;

use core\navigation\Mount;
use core\route\Path;
use core\route\Route;

class StaticMount implements Mount {
    use MountingPoint;



    public function __construct(
        protected string $alias
    ) {}



    public function getAlias(): string {
        return $this->alias;
    }

    public function transform(Route $route): Path {
        return $route->toStaticPath();
    }
}
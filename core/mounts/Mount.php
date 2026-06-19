<?php

namespace core\mounts;

use core\route\Path;
use core\route\Route;

interface Mount {
    public function getAlias(): string;

    public function getMountingPoint(): Route;

    public function setMountingPoint(Route $route): void;

    public function transform(Route $route): Path;
}
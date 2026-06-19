<?php

namespace core\navigation;

use core\route\Path;

interface Destination {
    public function getPathToSelf(string $alias): Path;
}
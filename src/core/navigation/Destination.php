<?php

namespace core\navigation;

use core\route\Route;

interface Destination {
    public function getRouteToSelf(string $alias): Route;
}
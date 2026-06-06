<?php

namespace core\mounts;

use core\route\Route;

trait MountingPoint {
    protected Route $mountingPoint;



    public function getMountingPoint(): Route {
        return $this->mountingPoint->copy();
    }

    public function setMountingPoint(Route $route): void {
        $this->mountingPoint = $route;
    }
}
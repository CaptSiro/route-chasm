<?php

namespace core\mounts;

use core\route\Route;

trait MountLocation {
    /**
     * @var array<string, Mount>
     */
    protected static array $routes;

    public static function mount(Mount $mount, Route|string $route): Route {
        $route = Route::resolve($route);

        $mount->setMountingPoint($route);
        self::$routes[$mount->getAlias()] = $mount;

        return $route;
    }

    public static function locate(string $alias): ?Mount {
        return self::$routes[$alias];
    }
}
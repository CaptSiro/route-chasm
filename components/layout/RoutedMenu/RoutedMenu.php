<?php

namespace components\layout\RoutedMenu;

use components\layout\Menu\Menu;
use core\route\Path;
use core\route\Router;

class RoutedMenu extends Menu {
    public static function from(Router $router, ?Path $path = null): static {
        return new static(
            RoutedMenuItem::from($router),
            $path ?? $router->getRoute()->toStaticPath()
        );
    }
}
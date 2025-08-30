<?php

namespace components\Home;

use components\core\HtmlHead\HtmlHead;
use components\core\Icon;
use components\core\Menu\Menu;
use components\core\Message\Message;
use components\core\RoutedMenu\RoutedMenu;
use components\core\WebPage\ContextAwareWebPage;
use core\route\Route;
use core\route\RouteNode;
use core\view\ContainerContent;

class Home extends ContainerContent {
    protected Menu $menu;

    public function __construct() {
        parent::__construct(
            new ContextAwareWebPage(
                head: new HtmlHead("Home")
            )
        );
    }



    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint
            ->getRouter()
            ->get('/home');

        $router->use(
            Route::menu("/Foo")
                ->icon("Foo", Icon::nf('nf-md-web')),
            new Message("Foo")
        );

        $router->use(
            Route::menu("/Foo/Bar")
                ->icon("Bar", Icon::nf('nf-md-laptop_account')),
            new Message("Bar")
        );

        $router->use(
            Route::menu("/Fizz Buzz")
                ->icon("Fizz Buzz", Icon::nf('nf-cod-settings_gear')),
            new Message("Fizz Buzz")
        );

        $this->menu = RoutedMenu::from($router);
    }
}
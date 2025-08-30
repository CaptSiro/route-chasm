<?php

namespace components\Home;

use components\core\HtmlHead\HtmlHead;
use components\core\Menu\Menu;
use components\core\WebPage\ContextAwareWebPage;
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
}
<?php

namespace components\core\Admin\Home;

use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Icon;
use core\view\ContainerContent;

class AdminHome extends ContainerContent {
    public static function changeHomeLabel(BreadCrumbs $crumbs): BreadCrumbs {
        $items = $crumbs->getItems();
        if (empty($items)) {
            return $crumbs;
        }

        $items[0]->setLabel(Icon::home());
        return $crumbs;
    }
}
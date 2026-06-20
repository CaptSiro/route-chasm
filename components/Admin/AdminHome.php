<?php

namespace components\Admin;

use components\Icon;
use components\layout\BreadCrumbs\BreadCrumbs;
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
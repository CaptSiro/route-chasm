<?php

namespace components\Admin;

use components\Icon;
use components\layout\BreadCrumbs\BreadCrumbs;
use core\view\Controller;

class AdminHome extends Controller {
    public static function changeHomeLabel(BreadCrumbs $crumbs): BreadCrumbs {
        $items = $crumbs->getItems();
        if (empty($items)) {
            return $crumbs;
        }

        $items[0]->setLabel(Icon::home());
        return $crumbs;
    }
}
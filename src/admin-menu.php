<?php

use components\core\Admin\Home\AdminHome;
use components\core\Admin\Menu\AdminMenu;
use components\core\Icon;
use components\core\Modules\Modules;


AdminMenu::getInstance()
    ->addItem('/', new AdminHome())
    ->setHomeLabel(null)
    ->addItem('/Modules', new Modules());

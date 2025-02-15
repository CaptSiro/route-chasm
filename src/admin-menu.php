<?php

use components\core\Admin\Editor\AdminEditor;
use components\core\Admin\Home\AdminHome;
use components\core\Admin\Menu\AdminMenu;
use components\core\Modules\Modules;
use entities\core\Domain;


AdminMenu::getInstance()
    ->addItem('/', new AdminHome())->setHomeLabel(null)
    ->addItem('/Modules', new Modules())
    ->addItem('/Domains', new AdminEditor(Domain::class))
;

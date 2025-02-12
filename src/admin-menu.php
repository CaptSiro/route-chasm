<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Modules\Modules;

AdminMenu::getInstance()
    ->addItem('Modules', new Modules());

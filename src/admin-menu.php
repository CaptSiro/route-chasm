<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Message\Message;
use components\core\Modules\Modules;


AdminMenu::getInstance()
    ->addItem('/', new Message("Home"))
    ->addItem('/Modules', new Modules());

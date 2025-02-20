<?php

use components\core\Admin\Editor\AdminEditor;
use components\core\Admin\Menu\AdminMenu;
use components\core\Modules\Modules;
use entities\core\Domain;

AdminMenu::getInstance()
    ->add('/Modules', new Modules())
    ->add('/Domains', new AdminEditor(Domain::getSchema()))
;

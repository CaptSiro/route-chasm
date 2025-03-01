<?php

use components\core\Admin\Editor\AdminEditor;
use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Message\Message;
use components\core\Modules\Modules;
use entities\core\Domain;

AdminMenu::getInstance()
    ->add('/Domains', new AdminEditor(Domain::getSchema()))
    ->add('/Monitoring/Modules', new Modules())
    ->add('/Monitoring/Tests/RouteChasm', new SptfTests(__DIR__ .'/../src/tests/cases/RouteChasm'))
    ->add('/Sub Menu/Item', new Message('Item'))
    ->add('/Sub Menu/Item 2', new Message('Item 2'))
;

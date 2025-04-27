<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Modules\Modules;
use components\layout\Grid\description\GridDescription;
use core\database\sql\ModelDescription;
use models\core\Domain\Domain;
use models\core\Setting\Setting;
use modules\forms\description\FormDescription;

AdminMenu::getInstance()
    ->add(
        '/Domains',
        new AdminNexus(
            ModelDescription::extract(Domain::class),
            FormDescription::extract(Domain::class),
            Domain::getGridDescription()
        )
    )
    ->addIcon('Domains', Icon::nf('nf-md-web'))

    ->add(
        '/System/Settings',
        new AdminNexus(
            ModelDescription::extract(Setting::class),
            FormDescription::extract(Setting::class),
            GridDescription::extract(Setting::class)
        )
    )
    ->addIcon('System', Icon::nf('nf-md-console'))
    ->addIcon('Settings', Icon::nf('nf-cod-settings_gear'))

    ->add('/System/Modules', new Modules())
    ->addIcon('Modules', Icon::nf('nf-md-package_variant'))

    ->add('/System/Tests/RouteChasm', new SptfTests(__DIR__ .'/../src/tests/cases/RouteChasm'))
    ->addIcon('Tests', Icon::nf('nf-md-beaker_check_outline'))
;

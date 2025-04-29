<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Modules\Modules;
use components\layout\Grid\description\GridDescription;
use core\database\sql\ModelDescription;
use models\core\Domain\Domain;
use models\core\Group;
use models\core\Privilege\Privilege;
use models\core\Resource;
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

    ->addIcon('System', Icon::nf('nf-md-console'))

    ->add(
        '/System/Groups',
        new AdminNexus(
            ModelDescription::extract(Group::class),
            FormDescription::extract(Group::class),
            GridDescription::extract(Group::class),
        )
    )

    ->add(
        '/System/Privileges',
        new AdminNexus(
            ModelDescription::extract(Privilege::class),
            FormDescription::extract(Privilege::class),
            GridDescription::extract(Privilege::class),
        )
    )

    ->add(
        '/System/User resources',
        new AdminNexus(
            ModelDescription::extract(Resource::class),
            FormDescription::extract(Resource::class),
            GridDescription::extract(Resource::class),
        )
    )

    ->add(
        '/System/Settings',
        new AdminNexus(
            ModelDescription::extract(Setting::class),
            FormDescription::extract(Setting::class),
            GridDescription::extract(Setting::class)
        )
    )
    ->addIcon('Settings', Icon::nf('nf-cod-settings_gear'))

    ->add('/System/Modules', new Modules())
    ->addIcon('Modules', Icon::nf('nf-md-package_variant'))

    ->add('/System/Tests/RouteChasm', new SptfTests(__DIR__ .'/../src/tests/cases/RouteChasm'))
    ->addIcon('Tests', Icon::nf('nf-md-beaker_check_outline'))
;

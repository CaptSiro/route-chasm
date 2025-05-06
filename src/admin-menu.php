<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Modules\Modules;
use components\layout\Grid\description\GridDescription;
use core\database\sql\ModelDescription;
use core\forms\description\FormDescription;
use models\core\Domain\Domain;
use models\core\Group\Group;
use models\core\Privilege\Privilege;
use models\core\Resource;
use models\core\Setting\Setting;
use models\core\User\User;
use models\core\User\UserEditorBehavior;

AdminMenu::getInstance()
    ->add(
        '/Domains',
        new AdminNexus(
            ModelDescription::extract(Domain::class),
            FormDescription::getEditor(Domain::class),
            Domain::getGridDescription()
        )
    )
    ->addIcon('Domains', Icon::nf('nf-md-web'))

    ->addIcon('System', Icon::nf('nf-md-console'))

    ->add(
        '/System/Users',
        new AdminNexus(
            ModelDescription::extract(User::class),
            UserEditorBehavior::getEditor(),
            GridDescription::extract(User::class),
        )
    )
    ->addIcon('Users', Icon::nf('nf-fa-user'))

    ->add(
        '/System/Groups',
        new AdminNexus(
            ModelDescription::extract(Group::class),
            FormDescription::getEditor(Group::class),
            GridDescription::extract(Group::class),
        )
    )
    ->addIcon('Groups', Icon::nf('nf-fa-group'))

    ->add(
        '/System/Privileges',
        new AdminNexus(
            ModelDescription::extract(Privilege::class),
            FormDescription::getEditor(Privilege::class),
            GridDescription::extract(Privilege::class),
        )
    )
    ->addIcon('Privileges', Icon::nf('nf-cod-settings'))

    ->add(
        '/System/User resources',
        new AdminNexus(
            ModelDescription::extract(Resource::class),
            FormDescription::getEditor(Resource::class),
            GridDescription::extract(Resource::class),
        )
    )
    ->addIcon('User resources', Icon::nf('nf-md-laptop_account'))

    ->add(
        '/System/Settings',
        new AdminNexus(
            ModelDescription::extract(Setting::class),
            FormDescription::getEditor(Setting::class),
            GridDescription::extract(Setting::class)
        )
    )
    ->addIcon('Settings', Icon::nf('nf-cod-settings_gear'))

    ->add('/System/Modules', new Modules())
    ->addIcon('Modules', Icon::nf('nf-md-package_variant'))

    ->add('/System/Tests/RouteChasm', new SptfTests(__DIR__ .'/../src/tests/cases/RouteChasm'))
    ->addIcon('Tests', Icon::nf('nf-md-beaker_check_outline'))
    ->addIcon('RouteChasm', Icon::nf('nf-md-alpha_r_box'))
;

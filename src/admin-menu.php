<?php

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Modules\Modules;
use entities\core\Domain\Domain;
use models\core\Domain\Domain as DomainModel;

AdminMenu::getInstance()
    ->add(
        '/Domains',
        (new AdminNexus(Domain::getSchema()))
            ->setGridLayout(Domain::defaultTableLayout())
    )
    ->addIcon('Domains', Icon::nf('nf-md-web'))

    ->add(
        '/Domains V2',
        new \components\core\Admin\Nexus_v2\AdminNexus(
            \core\database_v3\sql\ModelDescription::extract(DomainModel::class),
            \modules\forms\description\FormDescription::extract(DomainModel::class),
            DomainModel::getGridDescription()
        )
    )
    ->addIcon('Domains V2', Icon::nf('nf-md-web'))

    ->add('/Monitoring/Modules', new Modules())
    ->addIcon('Monitoring', Icon::nf('nf-oct-graph'))
    ->addIcon('Modules', Icon::nf('nf-md-package_variant'))

    ->add('/Monitoring/Tests/RouteChasm', new SptfTests(__DIR__ .'/../src/tests/cases/RouteChasm'))
    ->addIcon('Tests', Icon::nf('nf-md-beaker_check_outline'))
;

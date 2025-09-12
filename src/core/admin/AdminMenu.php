<?php

namespace core\admin;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Menu\Menu;
use components\core\Modules\Modules;
use components\core\RoutedMenu\RoutedMenu;
use components\layout\Grid\description\GridDescription;
use core\database\sql\ModelDescription;
use core\forms\description\FormDescription;
use core\route\Route;
use core\route\Router;
use models\core\Domain\Domain;
use models\core\Group\Group;
use models\core\Language\Language;
use models\core\Language\LanguageEditorBehavior;
use models\core\Page\Page;
use models\core\Resource;
use models\core\Setting\Setting;
use models\core\User\User;
use models\core\User\UserEditorBehavior;

class AdminMenu {
    public static function createMenu(Router $router): Menu {
        $router

            ->use(
                Route::menu("/Web/Page")
                    ->icon("Web", Icon::nf('nf-md-web'))
                    ->icon("Page", Icon::nf('nf-md-file_document')),
                Page::getNexus()
            )

            ->use(
                Route::menu("/Domains")
                    ->icon("Domains", Icon::nf('nf-md-web')),
                new AdminNexus(
                    ModelDescription::extract(Domain::class),
                    FormDescription::getEditor(Domain::class),
                    Domain::getGridDescription()
                )
            )

            ->use(
                Route::menu("/Languages")
                    ->icon("Languages", Icon::nf("nf-fa-language")),
                new AdminNexus(
                    ModelDescription::extract(Language::class),
                    LanguageEditorBehavior::getEditor(),
                    Language::getGridDescription(),
                    createButtonLabel: 'Add'
                )
            )

            ->use(
                Route::menu('/System/Users')
                    ->icon("System", Icon::nf('nf-md-console'))
                    ->icon('Users', Icon::nf('nf-fa-user')),
                new AdminNexus(
                    ModelDescription::extract(User::class),
                    UserEditorBehavior::getEditor(),
                    GridDescription::extract(User::class),
                )
            )

            ->use(
                Route::menu('/System/Groups')
                    ->icon('Groups', Icon::nf('nf-fa-group')),
                new AdminNexus(
                    ModelDescription::extract(Group::class),
                    FormDescription::getEditor(Group::class),
                    GridDescription::extract(Group::class),
                )
            )

            ->use(
                Route::menu('/System/User resources')
                    ->icon('User resources', Icon::nf('nf-md-laptop_account')),
                new AdminNexus(
                    ModelDescription::extract(Resource::class),
                    FormDescription::getEditor(Resource::class),
                    GridDescription::extract(Resource::class),
                )
            )

            ->use(
                Route::menu('/System/Settings')
                    ->icon('Settings', Icon::nf('nf-cod-settings_gear')),
                new AdminNexus(
                    ModelDescription::extract(Setting::class),
                    FormDescription::getEditor(Setting::class),
                    GridDescription::extract(Setting::class)
                )
            )

            ->use(
                Route::menu('/System/Modules')
                    ->icon('Modules', Icon::nf('nf-md-package_variant')),
                new Modules()
            )

            ->use(
                Route::menu('/System/Tests/RouteChasm')
                    ->icon('Tests', Icon::nf('nf-md-beaker_check_outline'))
                    ->icon('RouteChasm', Icon::nf('nf-md-alpha_r_box')),
                new SptfTests(__DIR__ .'/../../tests/cases/RouteChasm')
            );

        return RoutedMenu::from($router);
    }
}
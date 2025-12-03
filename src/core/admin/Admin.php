<?php

namespace core\admin;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\PhpInfo\PhpInfo;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Icon;
use components\core\Menu\Menu;
use components\core\Modules\Modules;
use components\core\RoutedMenu\RoutedMenu;
use components\layout\Grid\description\GridDescription;
use core\database\sql\ModelDescription;
use core\forms\description\FormDescription;
use core\fs\FileSystem;
use core\mounts\Mount;
use core\route\Route;
use core\route\Router;
use models\core\Domain\Domain;
use models\core\Group\Group;
use models\core\Language\Language;
use models\core\Language\LanguageEditorBehavior;
use models\core\Language\Lexicon\Phrase;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use models\core\Resource;
use models\core\Setting\Setting;
use models\core\User\User;
use models\core\User\UserEditorBehavior;

class Admin {
    private static Mount $mount;

    public static function mount(Mount $mount, Route|string $route): Route {
        $mount->setMountingPoint($route = Route::resolve($route));
        self::$mount = $mount;
        return $route;
    }

    public static function getMount(): Mount {
        return self::$mount;
    }



    public static function createMenu(Router $router): Menu {
        $router

            ->use(
                Route::menu("Web/Status")
                    ->icon("Web", Icon::nf('nf-md-web'))
                    ->icon("Status", Icon::nf('nf-md-checkbox_multiple_marked_circle')),
                new AdminNexus(
                    ModelDescription::extract(PageStatus::class),
                    FormDescription::getEditor(PageStatus::class),
                    GridDescription::extract(PageStatus::class)
                )
            )

            ->use(
                Route::menu("/Web/Pages")
                    ->icon("Pages", Icon::nf('nf-md-file_document')),
                Page::getNexus()
            )

            ->use(
                Route::menu("/Files")
                    ->icon("Files", Icon::nf('nf-fa-folder')),
                FileSystem::getNexus()
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
                Route::menu("/Localization/Languages")
                    ->icon("Localization", Icon::nf("nf-fa-language"))
                    ->icon("Languages", Icon::nf("nf-md-book_alphabet")),
                new AdminNexus(
                    ModelDescription::extract(Language::class),
                    LanguageEditorBehavior::getEditor(),
                    Language::getGridDescription(),
                    createButtonLabel: 'Add'
                )
            )

            ->use(
                Route::menu("/Localization/Vocabulary")
                    ->icon("Vocabulary", Icon::nf("nf-cod-book")),
                Phrase::getNexus()
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
                Route::menu('/System/PHP')
                    ->icon('PHP', Icon::nf('nf-dev-php')),
                new PhpInfo()
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
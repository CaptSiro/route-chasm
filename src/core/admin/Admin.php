<?php

namespace core\admin;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\PhpInfo\PhpInfo;
use components\core\Admin\SptfTests\SptfTests;
use components\core\Admin\User\AdminUserEditor;
use components\core\Icon;
use components\core\Menu\Menu;
use components\core\Modules\Modules;
use components\core\RoutedMenu\RoutedMenu;
use components\docs\Docs;
use components\docs\DocsDashboard;
use components\layout\Grid\description\GridDescription;
use core\App;
use core\database\sql\ModelDescription;
use core\forms\description\FormDescription;
use core\fs\FileSystem;
use core\mounts\Mount;
use core\route\Route;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\url\Url;
use models\core\Domain\Domain;
use models\core\fs\ImageVariantBehavior;
use models\core\fs\ImageVariantTransformer;
use models\core\Group\Group;
use models\core\Group\GroupBehavior;
use models\core\Language\Language;
use models\core\Language\LanguageEditorBehavior;
use models\core\Language\Lexicon\Phrase;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use models\core\Privilege\Privilege;
use models\core\Setting\Setting;
use models\core\User\User;
use models\core\User\UserEditorBehavior;
use models\core\UserResource;
use models\extensions\IsDefault\IsDefaultExtension;

class Admin {
    private static ?Mount $mount = null;

    public static function mount(Mount $mount, Route|string $route): Route {
        $mount->setMountingPoint($route = Route::resolve($route));
        self::$mount = $mount;
        return $route;
    }

    public static function getMount(): ?Mount {
        return self::$mount;
    }

    public static function getUrl(): ?Url {
        if (is_null($mount = self::getMount())) {
            return null;
        }

        return App::getInstance()
            ->getRequest()
            ->getDomain()
            ->createUrl($mount->getMountingPoint()->toStaticPath());
    }



    protected static function createMenuWeb(Router $router): void {
        $web = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_PAGE);

        $status = new AdminNexus(
            ModelDescription::extract(PageStatus::class),
            FormDescription::getEditor(PageStatus::class),
            GridDescription::extract(PageStatus::class),
        );
        $status->setUserResource($web);
        $status->setRouter(
            Route::menu("Web/Status")
                ->icon("Web", Icon::nf('nf-md-web'))
                ->icon("Status", Icon::nf('nf-md-checkbox_multiple_marked_circle')),
            $router
        );

        Page::getNexus()
            ->setUserResource($web)
            ->setRouter(
                Route::menu("/Web/Pages")
                    ->icon("Pages", Icon::nf('nf-md-file_document')),
                $router
            );
    }

    protected static function createMenuFileSystem(Router $router): void {
        FileSystem::setRouter(
            Route::menu("/File System/Files")
                ->icon("File System", Icon::nf('nf-fa-folder'))
                ->icon("Files", Icon::nf('nf-fa-file')),
            $router
        );

        $imageVariants = new AdminNexus(
            ModelDescription::extract(ImageVariantTransformer::class),
            ImageVariantBehavior::createEditor(),
            ImageVariantTransformer::getGridLayoutFactory(),
        );
        $imageVariants->setUserResource(UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_FILE_SYSTEM));
        $imageVariants->setRouter(
            Route::menu("/File System/Image Variants")
                ->icon("Image Variants", Icon::nf('nf-md-file_image_plus')),
            $router
        );
    }

    protected static function createMenuLocalization(Router $router): void {
        $localization = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_LOCALIZATION);

        $language = new AdminNexus(
            ModelDescription::extract(Language::class),
            LanguageEditorBehavior::getEditor(),
            Language::getGridDescription(),
            createButtonLabel: 'Add'
        );
        $language->addExtension(new IsDefaultExtension());
        $language->setUserResource($localization);
        $language->setRouter(
            Route::menu("/Localization/Languages")
                ->icon("Localization", Icon::nf("nf-fa-language"))
                ->icon("Languages", Icon::nf("nf-md-book_alphabet")),
            $router
        );

        Phrase::getNexus()
            ->setUserResource($localization)
            ->setRouter(
                Route::menu("/Localization/Vocabulary")
                    ->icon("Vocabulary", Icon::nf("nf-cod-book")),
                $router
            );
    }

    protected static function createMenuSystem(Router $router): void {
        $user = User::fromRequest(App::getInstance()
            ->getRequest());
        $read = Privilege::fromName(Privilege::READ);

        $system = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_SYSTEM);

        $users = new AdminNexus(
            ModelDescription::extract(User::class),
            new AdminUserEditor(new UserEditorBehavior()),
            GridDescription::extract(User::class),
        );
        $users->setUserResource($system);
        $users->setRouter(
            Route::menu('/System/Users')
                ->icon("System", Icon::nf('nf-md-console'))
                ->icon('Users', Icon::nf('nf-fa-user')),
            $router
        );

        $groups = new AdminNexus(
            ModelDescription::extract(Group::class),
            GroupBehavior::getEditor(),
            GridDescription::extract(Group::class),
        );
        $groups->setUserResource($system);
        $groups->setRouter(
            Route::menu('/System/Groups')
                ->icon('Groups', Icon::nf('nf-fa-group')),
            $router
        );

        $userResources = new AdminNexus(
            ModelDescription::extract(UserResource::class),
            FormDescription::getEditor(UserResource::class),
            GridDescription::extract(UserResource::class),
        );
        $userResources->setUserResource($system);
        $userResources->setRouter(
            Route::menu('/System/User resources')
                ->icon('User resources', Icon::nf('nf-md-laptop_account')),
            $router
        );

        $settings = new AdminNexus(
            ModelDescription::extract(Setting::class),
            FormDescription::getEditor(Setting::class),
            GridDescription::extract(Setting::class),
        );
        $settings->setUserResource($system);
        $settings->setRouter(
            Route::menu('/System/Settings')
                ->icon('Settings', Icon::nf('nf-cod-settings_gear')),
            $router
        );

        if (!is_null($user) && $user->hasAccess($system, $read)) {
            $router->use(
                Route::menu('/System/Modules')
                    ->icon('Modules', Icon::nf('nf-md-package_variant')),
                (new Modules())
                    ->setUserResource($system)
            );

            $router->use(
                Route::menu('/System/PHP')
                    ->icon('PHP', Icon::nf('nf-dev-php')),
                (new PhpInfo())
                    ->setUserResource($system)
            );

            $router->use(
                Route::menu('/System/Tests/RouteChasm')
                    ->icon('Tests', Icon::nf('nf-md-beaker_check_outline'))
                    ->icon('RouteChasm', Icon::nf('nf-md-alpha_r_box')),
                (new SptfTests(__DIR__ .'/../../tests/cases/RouteChasm'))
                    ->setUserResource($system)
            );
        }
    }

    protected static function createMenuDocs(Router $router): void {
        if (!Docs::getInstance()->isBound()) {
            return;
        }

        $user = User::fromRequest(App::getInstance()
            ->getRequest());
        $read = Privilege::fromName(Privilege::READ);

        $docs = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOCS_ADMIN);

        if (is_null($user) || !$user->hasAccess($docs, $read)) {
            return;
        }

        $router->use(
            Route::menu('/Docs')
                ->icon('Docs', Icon::nf('nf-md-file_document_multiple')),
            (new DocsDashboard())
                ->setUserResource($docs)
        );
    }

    public static function createMenu(Router $router): Menu {
        self::createMenuWeb($router);
        self::createMenuFileSystem($router);
        self::createMenuDocs($router);

        $domains = new AdminNexus(
            ModelDescription::extract(Domain::class),
            FormDescription::getEditor(Domain::class),
            Domain::getGridDescription(),
        );
        $domains->setUserResource(UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOMAIN));
        $domains->setRouter(
            Route::menu("/Domains")
                ->icon("Domains", Icon::nf('nf-md-web')),
            $router
        );

        self::createMenuLocalization($router);
        self::createMenuSystem($router);

        return RoutedMenu::from($router);
    }
}
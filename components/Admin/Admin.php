<?php

namespace components\Admin;

use components\Admin\Phrase\PhrasePacks;
use components\Admin\SptfTests\SptfTests;
use components\docs\Docs;
use components\docs\DocsDashboard;
use components\forms\description\FormDescription;
use components\layout\Dashboard\Dashboard;
use components\layout\Dashboard\DashboardPageView;
use components\layout\Dashboard\DashboardSideBar;
use components\layout\Dashboard\DashboardSideBarItem;
use components\Icon;
use components\layout\Grid\description\GridDescription;
use components\layout\Menu\Menu;
use components\Message\Message;
use components\Message\MessageType;
use components\Modules\Modules;
use components\nexus\Nexus;
use components\Project;
use components\windows\LanguageSelect;
use core\collections\Views;
use core\database\sql\ModelDescription;
use core\fs\FileSystem;
use core\route\Path;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\Singleton;
use core\view\View;
use models\Domain\Domain;
use models\fs\ImageVariantBehavior;
use models\fs\ImageVariantTransformer;
use models\Group\Group;
use models\Group\GroupBehavior;
use models\Language\Language;
use models\Language\LanguageEditorBehavior;
use models\Language\Lexicon\Phrase;
use models\Page\Page;
use models\Page\PageStatus;
use models\Setting\Setting;
use models\User\User;
use models\User\UserEditorBehavior;
use models\UserResource;

class Admin extends Dashboard {
    use Singleton;

    public const LEXICON_GROUP = 'dashboard.admin';



    public function __construct() {
        parent::__construct(
            DashboardPageView::fromDashboardComponent(
                $this, (new AdminHome())->setTitle('Admin Home')
            )
        );

        $this->createRoutes();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    protected function createMenuWeb(): void {
        $web = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_PAGE);

        $this->add(
            Route::menu("Web/Status")
                ->icon("Web", Icon::nf('nf-md-web'))
                ->icon("Status", Icon::nf('nf-md-checkbox_multiple_marked_circle')),
            Nexus::fromModel(PageStatus::class),
            $web
        );

        $this->add(
            Route::menu("/Web/Pages")
                ->icon("Pages", Icon::nf('nf-md-file_document')),
            Page::getNexus(),
            $web
        );
    }

    protected function createMenuFileSystem(): void {
        $fs = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_FILE_SYSTEM);

        $this->add(
            Route::menu("/File System/Files")
                ->icon("File System", Icon::nf('nf-fa-folder'))
                ->icon("Files", Icon::nf('nf-fa-file')),
            FileSystem::getNexus(),
            FileSystem::getUserResource()
        );

        $this->add(
            Route::menu("/File System/Image Variants")
                ->icon("Image Variants", Icon::nf('nf-md-file_image_plus')),
            Nexus::fromBehavior(
                ModelDescription::extract(ImageVariantTransformer::class),
                new ImageVariantBehavior(),
                ImageVariantTransformer::getGridLayoutFactory()
            ),
            $fs
        );
    }

    protected function createMenuLocalization(): void {
        $localization = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_LOCALIZATION);

        $this->add(
            Route::menu("/Localization/Languages")
                ->icon("Localization", Icon::nf("nf-fa-language"))
                ->icon("Languages", Icon::nf("nf-md-book_alphabet")),
            Nexus::fromBehavior(
                ModelDescription::extract(Language::class),
                new LanguageEditorBehavior(),
                Language::getGridDescription(),
                'add'
            )->addExtension(Language::getIsDefaultExtension()),
            $localization
        );

        $this->add(
            Route::menu("/Localization/Vocabulary")
                ->icon("Vocabulary", Icon::nf("nf-cod-book")),
            Phrase::getNexus(),
            $localization
        );

        $this->add(
            Route::menu('/Localization/Translation Packs')
                ->icon('Translation Packs', Icon::nf('nf-md-package_variant')),
            new PhrasePacks(),
            $localization
        );
    }

    protected function createMenuSystem(): void {
        $system = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_SYSTEM);

        $this->add(
            Route::menu('/System/Users')
                ->icon("System", Icon::nf('nf-md-console'))
                ->icon('Users', Icon::nf('nf-fa-user')),
            Nexus::fromEditor(
                $userModelDescription = ModelDescription::extract(User::class),
                new UserNexusEditor($userModelDescription, new UserEditorBehavior()),
                GridDescription::extract(User::class),
            ),
            $system
        );

        $this->add(
            Route::menu('/System/Groups')
                ->icon('Groups', Icon::nf('nf-fa-group')),
            Nexus::fromBehavior(
                ModelDescription::extract(Group::class),
                new GroupBehavior(),
                GridDescription::extract(Group::class),
            ),
            $system
        );

        $this->add(
            Route::menu('/System/User resources')
                ->icon('User resources', Icon::nf('nf-md-laptop_account')),
            Nexus::fromModel(UserResource::class),
            $system
        );

        $this->add(
            Route::menu('/System/Settings')
                ->icon('Settings', Icon::nf('nf-cod-settings_gear')),
            Nexus::fromModel(Setting::class),
            $system
        );

        $this->add(
            Route::menu('/System/Modules')
                ->icon('Modules', Icon::nf('nf-md-package_variant')),
            $this->createPageView()->setComponent(new Modules()),
            $system
        );

        $this->add(
            Route::menu('/System/PHP')
                ->icon('PHP', Icon::nf('nf-dev-php')),
            $this->createPageView()->setComponent((new PhpInfo())),
            $system
        );

        $this->add(
            Route::menu('/System/Tests/RouteChasm')
                ->icon('Tests', Icon::nf('nf-md-beaker_check_outline'))
                ->icon('RouteChasm', Icon::nf('nf-md-alpha_r_box')),
            $this->createPageView()->setComponent(
                new SptfTests(Path::join(DIRECTORY_FRAMEWORK, '/tests/cases'))
            ),
            $system
        );
    }

    protected function createMenuDocs(): void {
        if (!Docs::getInstance()->isBound()) {
            return;
        }

        $docs = UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOCS_ADMIN);

        $this->add(
            Route::menu('/Docs')
                ->icon('Docs', Icon::nf('nf-md-file_document_multiple')),
            $this->createPageView()->setComponent(new DocsDashboard()),
            $docs
        );
    }

    public function createRoutes(): void {
        $this->createMenuWeb();
        $this->createMenuFileSystem();
        $this->createMenuDocs();

        $this->add(
            Route::menu("/Domains")
                ->icon("Domains", Icon::nf('nf-md-web')),
            Nexus::fromBehavior(
                ModelDescription::extract(Domain::class),
                FormDescription::extract(Domain::class),
                Domain::getGridDescription(),
            ),
            UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOMAIN)
        );

        $this->createMenuLocalization();
        $this->createMenuSystem();


        $this->add(
            Route::menu("/Test")
                ->icon("Test", Icon::nf('nf-md-web')),
            DashboardPageView::fromDashboardComponent($this, new Message('Test', MessageType::INFO)),
            UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOMAIN)
        );
    }



    // Dashboard
    public function createDashboardSideBar(Menu $menu): View {
        $ret = new DashboardSideBar($menu);

        $ret->setTemplateSlot($ret::SLOT_HEADER, new Project());
        $ret->setTemplateSlot($ret::SLOT_FOOTER, new Views([
            $languageWindow = LanguageSelect::window(openButton: false),
            DashboardSideBarItem::button(
                "window_open($('#". $languageWindow->getId() ."'))",
                $this->tr("Languages"),
                Icon::nf('nf-fa-language')
            ),

            $this->getDashboardLogin()->createLogoutSideBarItem()
        ]));

        return $ret;
    }

    public function authenticate(?User $user): bool {
        return $user->isAdmin();
    }
}
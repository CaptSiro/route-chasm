<?php

namespace components\Admin;

use components\Admin\Nexus\AdminNexus;
use components\Admin\Phrase\AdminPhrasePacks;
use components\Admin\SptfTests\SptfTests;
use components\Dashboard\Dashboard;
use components\Dashboard\DashboardPageView;
use components\Dashboard\DashboardSideBar;
use components\Dashboard\DashboardSideBarItem;
use components\docs\Docs;
use components\docs\DocsDashboard;
use components\forms\description\FormDescription;
use components\html\HtmlHead;
use components\Icon;
use components\layout\Grid\description\GridDescription;
use components\layout\Menu\Menu;
use components\layout\RoutedMenu\RoutedMenu;
use components\Message\Message;
use components\Message\MessageType;
use components\Modules\Modules;
use components\windows\LanguageSelect;
use core\App;
use core\collections\Views;
use core\database\sql\ModelDescription;
use core\fs\FileSystem;
use core\route\Path;
use core\route\Route;
use core\route\Router;
use core\RouteChasmEnvironment;
use core\view\View;
use models\Domain\Domain;
use models\extensions\IsDefault\IsDefaultExtension;
use models\fs\ImageVariantBehavior;
use models\fs\ImageVariantTransformer;
use models\Group\Group;
use models\Group\GroupBehavior;
use models\Language\Language;
use models\Language\LanguageEditorBehavior;
use models\Language\Lexicon\Phrase;
use models\Page\Page;
use models\Page\PageStatus;
use models\Privilege\Privilege;
use models\Setting\Setting;
use models\User\User;
use models\User\UserEditorBehavior;
use models\UserResource;

class Admin extends Dashboard {
    public function __construct() {
        parent::__construct(
            new DashboardPageView($this, new HtmlHead('Admin Home'), new AdminHome())
        );

        $this->createRoutes();
    }



    public function createRoutes(): void {
        $this->add(
            Route::menu("/Test")
                ->icon("Test", Icon::nf('nf-md-web')),
            DashboardPageView::fromMessage($this, new Message('Test', MessageType::INFO)),
            UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_DOMAIN)
        );
    }



    // Dashboard
    public function createDashboardSideBar(Menu $menu): View {
        $ret = new DashboardSideBar($menu);

        $ret->setTemplateSlot($ret::SLOT_HEADER, new AdminProject($this));
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
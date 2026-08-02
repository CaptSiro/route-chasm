<?php

namespace components\Admin;

use components\layout\Dashboard\Dashboard;
use components\layout\Dashboard\DashboardPageView;
use components\layout\Dashboard\DashboardSideBar;
use components\layout\Dashboard\DashboardSideBarItem;
use components\Icon;
use components\layout\Menu\Menu;
use components\Message\Message;
use components\Message\MessageType;
use components\windows\LanguageSelect;
use core\collections\Views;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\view\View;
use models\User\User;
use models\UserResource;

class Admin extends Dashboard {
    public function __construct() {
        parent::__construct(
            DashboardPageView::fromDashboardComponent(
                $this, (new AdminHome())->setTitle('Admin Home')
            )
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
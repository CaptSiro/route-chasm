<?php

namespace core\admin;

use components\core\Admin\Login\AdminLogin;
use components\core\Menu\Menu;
use components\core\Message\Message;
use core\actions\Action;
use core\actions\Procedure;
use core\actions\When;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\Singleton;

/**
 * You may pass <code>Action</code> to <code>AdminRouter::getInstance</code> set as admin home page
 */
class AdminRouter extends Router {
    use Singleton;

    public const LEXICON_GROUP = 'admin';

    protected const KEY_IS_ADMIN = 'isAdmin';

    public static function isAdmin(Request $request): bool {
        return $request->exists(self::KEY_IS_ADMIN);
    }



    protected ?Path $path = null;
    protected Menu $menu;

    public function __construct(
        protected ?Action $home = null
    ) {
        parent::__construct();
    }



    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $this->menu = \core\admin\Admin::createMenu($this);

        $this->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_IS_ADMIN, true);
            }),
            // middleware
            new AdminLogin(),
            // if it is just / render message 'Admin Home'
            new When(
                fn(Request $request) => $request->getRemainingPath()->getDepth() === 0,
                $this->home ?? new Procedure(fn() => new Message('Admin Home'))
            ),
        );

        $this->use('/**',
            fn(Request $request, Response $response) => $response->sendMessage(
                'Not found',
                HttpCode::CE_NOT_FOUND
            )
        );
    }

    public function getPath(): Path {
        if (is_null($this->path)) {
            $this->path = Admin::getMount()->transform($this->getRoute());
        }

        return $this->path;
    }

    public function getMenu(): Menu {
        return $this->menu;
    }
}
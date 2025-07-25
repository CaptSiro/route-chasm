<?php

namespace core;

use components\core\Admin\Login\AdminLogin;
use components\core\Admin\Menu\AdminMenu;
use components\core\Message\Message;
use core\actions\Action;
use core\actions\Procedure;
use core\actions\When;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\Router;

/**
 * You may pass <code>Action</code> to <code>AdminRouter::getInstance</code> set as admin home page
 */
class AdminRouter extends Router {
    use Singleton;

    protected const KEY_IS_ADMIN = 'isAdmin';

    public static function isAdmin(Request $request): bool {
        return $request->exists(self::KEY_IS_ADMIN);
    }



    protected ?string $path = null;

    public function __construct(?Action $home = null) {
        parent::__construct();
        AdminMenu::load(App::getInstance()->getSource('admin-menu.php'));

        $this->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_IS_ADMIN, true);
            }),
            new AdminLogin(),
            new When(
                fn(Request $request) => $request->getRemainingPath()->getDepth() === 0,
                $home ?? new Procedure(fn() => new Message('Admin Home'))
            ),
        );

        $this->use('/**',
            AdminMenu::getInstance(),
            fn(Request $request, Response $response) => $response->sendMessage(
                'Not found',
                HttpCode::CE_NOT_FOUND
            )
        );
    }



    public function getPath(): string {
        if (is_null($this->path)) {
            $this->path = $this->getRoute()->toStaticPath()->toString();
        }

        return $this->path;
    }
}
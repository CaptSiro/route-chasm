<?php

namespace core\admin;

use components\core\Admin\Menu\AdminMenu;
use components\core\Explorer\Explorer;
use core\App;
use core\communication\Request;
use core\Router;
use core\Singleton;

class AdminRouter extends Router {
    use Singleton;



    protected const KEY_IS_ADMIN = 'isAdmin';

    public static function isAdmin(Request $request): bool {
        if (!$request->exists(self::KEY_IS_ADMIN)) {
            return false;
        }

        return boolval($request->get(self::KEY_IS_ADMIN));
    }



    public function __construct() {
        parent::__construct();

        $this->use('/', function (Request $request) {
            $request->set(self::KEY_IS_ADMIN, true);
            AdminMenu::load(App::getInstance()->getSource('admin-menu.php'));
        });

        $this->use('/', new Explorer(
            __DIR__,
            'admin',
            App::getInstance()->getRequest()->getUrl()->getRealPath(),
            false
        ));
    }



    public function isMiddleware(): bool {
        return true;
    }
}
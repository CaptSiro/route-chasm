<?php

namespace core;

use components\core\Admin\Menu\AdminMenu;
use components\core\Message\Message;
use core\communication\Request;
use core\communication\Response;
use core\endpoints\Endpoint;
use core\endpoints\Procedure;
use core\http\HttpCode;

/**
 * You may pass <code>Endpoint</code> to <code>AdminRouter::getInstance</code> set as admin home page
 */
class AdminRouter extends Router {
    use Singleton;

    protected const KEY_IS_ADMIN = 'isAdmin';

    public static function isAdmin(Request $request): bool {
        return $request->exists(self::KEY_IS_ADMIN);
    }



    protected ?string $path = null;

    public function __construct(?Endpoint $home = null) {
        parent::__construct();

        $this->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_IS_ADMIN, true);
                AdminMenu::load(App::getInstance()->getSource('admin-menu.php'));
            }),
            $home ?? new Message('Admin Home')
        );

        $this->use('/**', fn(Request $request, Response $response) => $response->sendMessage(
            'Not found',
            HttpCode::CE_NOT_FOUND
        ));
    }



    public function getPath(): string {
        if (is_null($this->path)) {
            $this->path = $this->getUrlPath();
        }

        return $this->path;
    }
}
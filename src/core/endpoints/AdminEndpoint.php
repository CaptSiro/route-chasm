<?php

namespace core\endpoints;

use components\core\Admin\Menu\AdminMenu;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\Singleton;

class AdminEndpoint implements Endpoint {
    use Singleton, SimpleEndpoint;



    protected const KEY_IS_ADMIN = 'isAdmin';

    public static function isAdmin(Request $request): bool {
        if (!$request->exists(self::KEY_IS_ADMIN)) {
            return false;
        }

        return boolval($request->get(self::KEY_IS_ADMIN));
    }



    protected ?string $path = null;



    public function isMiddleware(): bool {
        return true;
    }

    public function execute(Request $request, Response $response): void {
        $request->set(self::KEY_IS_ADMIN, true);
        AdminMenu::load(App::getInstance()->getSource('admin-menu.php'));

        $item = AdminMenu::getInstance()
            ->getItem($request->getAnyParam() ?? '');

        if (is_null($item)) {
            $response->error(
                'Page not found',
                HttpCode::CE_NOT_FOUND
            );
        }

        if ($item instanceof Endpoint) {
            $item->execute($request, $response);
            return;
        }

        $response->renderRoot($item);
    }

    public function getPath(): string {
        if (is_null($this->path)) {
            $this->path = $this->getUrlPath();
        }

        return $this->path;
    }
}
<?php

namespace core;

use Closure;
use components\core\Admin\MenuV2\AdminMenuV2;
use components\core\Message\Message;
use core\communication\Request;
use core\endpoints\Endpoint;
use core\endpoints\Procedure;
use core\path\Path;

/**
 * You may pass <code>Endpoint</code> to <code>AdminRouter::getInstance</code> set as admin home page
 */
class AdminRouter extends Router {
    use Singleton;

    protected const KEY_IS_ADMIN = 'isAdmin';



    protected ?string $path = null;

    public function __construct(?Endpoint $home = null) {
        parent::__construct();

        $this->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_IS_ADMIN, true);
                AdminMenuV2::load(App::getInstance()->getSource('admin-menu-v2.php'));
            }),
            $home ?? new Message('Admin Home')
        );
    }

    public function use(string|Path $path, Endpoint|Closure ...$endpoints): void {
        parent::use($path, ...$endpoints);
    }



    public function getPath(): string {
        if (is_null($this->path)) {
            $this->path = $this->getUrlPath();
        }

        return $this->path;
    }
}
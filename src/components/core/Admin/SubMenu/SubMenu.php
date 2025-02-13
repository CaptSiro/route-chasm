<?php

namespace components\core\Admin\SubMenu;

use components\core\Admin\Menu\AdminMenu;
use core\App;
use core\CssClass;
use core\endpoints\AdminEndpoint;
use core\view\Render;
use core\view\Renderer;

class SubMenu implements Render {
    use Renderer, CssClass;



    public function __construct(
        protected string $path,
        protected array $menu
    ) {}



    public function isEmpty(): bool {
        $keys = array_keys($this->menu);
        if (empty($keys)) {
            return true;
        }

        if (count($keys) === 1 && $keys[0] === AdminMenu::KEY_RENDER) {
            return true;
        }

        return false;
    }

    public function hasRender(string $label): bool {
        return isset($this->menu[$label][AdminMenu::KEY_RENDER]);
    }

    public function createUrl(string $label): ?string {
        $translated = AdminMenu::getInstance()
            ->translate($this->path .'/'. $label);

        if (is_null($translated)) {
            return null;
        }

        return App::getInstance()
            ->prependHome(AdminEndpoint::getInstance()->getPath() .'/'. $translated);
    }
}
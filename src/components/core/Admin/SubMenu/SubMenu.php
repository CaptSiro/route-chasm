<?php

namespace components\core\Admin\SubMenu;

use components\core\Admin\Menu\AdminMenu;
use core\CssClass;
use core\view\Render;
use core\view\Renderer;

class SubMenu implements Render {
    use Renderer, CssClass;



    public function __construct(
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
}
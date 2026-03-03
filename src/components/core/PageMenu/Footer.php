<?php

namespace components\core\PageMenu;

use core\view\Renderer;
use core\view\View;
use models\core\Menu;

class Footer implements View {
    use Renderer;



    public static function default(): static {
        return new static(
            PageMenu::fromModelName(Menu::NAME_FOOTER),
            PageMenu::fromModelName(Menu::NAME_LEGAL),
        );
    }



    public function __construct(
        protected PageMenu $menu,
        protected PageMenu $legal
    ) {}
}
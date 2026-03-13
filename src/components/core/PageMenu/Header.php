<?php

namespace components\core\PageMenu;

use components\core\Search\HeaderSearch;
use core\view\Renderer;
use core\view\View;
use models\core\Menu;

class Header implements View {
    use Renderer;



    public static function default(): static {
        return new static(
            PageMenu::fromModelName(Menu::NAME_HEADER),
            new HeaderSearch()
        );
    }



    public function __construct(
        protected PageMenu $menu,
        protected View $search
    ) {}
}
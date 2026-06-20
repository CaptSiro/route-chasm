<?php

namespace components\layout\PageMenu;

use components\Search\HeaderSearch;
use core\view\Renderer;
use core\view\View;
use core\view\ViewTemplate;
use models\Menu;

class Header implements ViewTemplate {
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
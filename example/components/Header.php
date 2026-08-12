<?php

namespace example\components;

use components\layout\PageMenu\PageMenu;
use components\Search\HeaderSearch;
use core\view\View;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\Menu;

class Header implements ViewTemplate {
    use ViewTemplateRenderer;



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
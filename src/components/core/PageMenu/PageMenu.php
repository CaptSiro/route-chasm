<?php

namespace components\core\PageMenu;

use components\core\Menu\Menu;
use core\App;
use core\route\Path;
use models\core\Language\Language;

class PageMenu extends Menu {
    public static function from(\models\core\Menu $menu, ?Language $language = null): static {
        $language ??= App::getInstance()->getRequest()->getLanguage();
        $root = new PageMenuItem('');

        foreach ($menu->getReleasedPages() as $item) {
            $pages = $item->getParents(true);
            $current = $root;

            foreach ($pages as $page) {
                $title = $page->getLocalization($language)->title;
                $current = $current->getChild($title, true);
            }

            $current->setItem($item);
        }

        return new static(
            $root,
            Path::empty()
        );
    }

    public static function fromModelName(string $name, ?Language $language = null): static {
        return static::from(\models\core\Menu::fromName($name), $language);
    }
}
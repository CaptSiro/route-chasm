<?php

namespace components\core\PageMenu;

use components\core\Html\Html;
use components\core\Menu\Menu;
use core\App;
use core\route\Path;
use models\core\Language\Language;
use RuntimeException;

class PageMenu extends Menu {
    public static function from(\models\core\Menu $menu, ?Language $language = null): static {
        $language ??= App::getInstance()->getRequest()->getLanguage();
        $root = new PageMenuItem('');

        foreach ($menu->getReleasedPages() as $item) {
            $pages = $item->getParents(true);
            $current = $root;

            foreach ($pages as $page) {
                $title = $page->getLocalizationOrDefault($language)->title;
                $current = $current->getChild($title, true);
            }

            $current->setItem($item);
        }

        return new static(
            $root,
            Path::empty()
        );
    }

    public static function fromModelName(string $name, ?Language $language = null, bool $create = true): static {
        $menu = \models\core\Menu::fromName($name);
        if (is_null($menu)) {
            if (!$create) {
                throw new RuntimeException('Menu not found: '. Html::escape($name));
            }

            $menu = new \models\core\Menu();
            $menu->name = $name;
            $menu->save();
        }

        return static::from($menu, $language);
    }
}
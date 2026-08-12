<?php

namespace core\view;

class PageViewFactory {
    private static PageViewFactory $factory;

    public static function setDefaultFactory(PageViewFactory $factory): void {
        self::$factory = $factory;
    }

    public static function getDefaultFactory(): PageViewFactory {
        if (!isset(self::$factory)) {
            self::$factory = new self();
        }

        return self::$factory;
    }

    public static function createFromComponent(Component $component): PageView {
        return self::getDefaultFactory()
            ->create()
            ->setComponent($component);
    }



    public function create(): PageView {
        return new PageView();
    }
}
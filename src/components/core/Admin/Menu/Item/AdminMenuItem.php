<?php

namespace components\core\Admin\Menu\Item;

use components\core\Menu\Item\MenuItem;
use core\AdminRouter;
use core\App;
use core\path\Path;

class AdminMenuItem extends MenuItem {
    public function __construct(
        protected array &$icons
    ) {
        $this->setTemplate(
            $this->getResource('AdminMenuItem.phtml')
        );
    }

    public function createUrl(): string {
        return App::getInstance()
            ->prependHome(
                Path::join(AdminRouter::getInstance()->getPath(), $this->path)
            );
    }

    public function getIcon(): string {
        if (!isset($this->icons[$this->label])) {
            return '';
        }

        return $this->icons[$this->label];
    }
}
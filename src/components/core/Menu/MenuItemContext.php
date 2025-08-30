<?php

namespace components\core\Menu;

trait MenuItemContext {
    protected Menu $context;

    public function setContext(Menu $menu): static {
        $this->context = $menu;
        return $this;
    }
}
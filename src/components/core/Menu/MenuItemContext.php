<?php

namespace components\core\Menu;

trait MenuItemContext {
    protected Menu $context;

    protected int $menuLevel;



    public function setContext(Menu $menu): static {
        $this->context = $menu;
        return $this;
    }

    public function getMenuLevel(): int {
        return $this->menuLevel;
    }

    public function setMenuLevel(int $level): void {
        $this->menuLevel = $level;
    }
}
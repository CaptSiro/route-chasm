<?php

namespace components\core\Menu;

use core\route\Path;
use core\view\View;

/**
 * @template T
 */
interface MenuItem extends View {
    public function setContext(Menu $menu): static;

    public function hasChildren(): bool;

    /**
     * @return array<MenuItem>
     */
    public function getChildren(): array;

    public function getLabel(): string;

    public function getMenuSegment(): Path;
}
<?php

namespace components\core\Menu;

use core\route\Path;
use core\view\View;

/**
 * @template T
 */
interface  MenuItem extends View {
    public function hasItem(): bool;

    public function getStateClasses(): string;

    public function setContext(Menu $menu): static;

    public function hasChildren(): bool;

    /**
     * @return array<MenuItem<T>>
     */
    public function getChildren(): array;

    public function getLabel(): string;

    public function getMenuSegment(): Path;

    public function getMenuLevel(): int;

    public function setMenuLevel(int $level): void;
}
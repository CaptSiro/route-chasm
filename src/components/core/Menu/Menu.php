<?php

namespace components\core\Menu;

use core\html\Attribute;
use core\html\HtmlAttribute;
use core\route\Path;
use core\view\Component;

/**
 * @template T
 */
class Menu extends Component implements Attribute {
    use HtmlAttribute;

    public const LEXICON_GROUP = 'menu';



    protected int $level = 0;
    protected bool $isInset = true;
    protected bool $isExpanded = false;
    /** @var Menu<T>|null  */
    protected ?Menu $root = null;
    protected ?Path $selected = null;

    /**
     * @param MenuItem<T> $item
     */
    public function __construct(
        protected MenuItem $item,
        protected ?Path $path = null
    ) {
        parent::__construct();
        $this->root = null;
        $this->path ??= Path::empty();
    }



    public function getStateClasses(): string {
        $classes = '';

        if ($this->item->hasItem()) {
            $classes .= ' has-item';
        }

        if ($this->item->hasChildren()) {
            $classes .= ' has-sub-menu';
        }

        if ($this->isSelectedLeaf()) {
            $classes .= ' selected';
        }

        return ltrim($classes);
    }

    public function setSelected(?Path $selected): void {
        $this->selected = $selected;
        $this->selected->rewind();
    }

    public function getPath(): ?Path {
        return $this->path;
    }

    public function setPath(?Path $path): void {
        $this->path = $path;
    }

    public function setIsInset(bool $bool): static {
        $this->isInset = $bool;
        return $this;
    }

    public function setLevel(int $level): void {
        $this->level = $level;
    }

    public function setIsExpanded(bool $isExpanded): static {
        $this->isExpanded = $isExpanded;
        return $this;
    }

    public function isRoot(): bool {
        return is_null($this->root);
    }

    public function isSelected(string $target): bool {
        if (is_null($this->selected) || $this->selected->valid()) {
            return false;
        }

        $current = $this->selected->current();
        return $current === $target;
    }

    public function isSelectedLeaf(): bool {
        if (is_null($this->selected)) {
            return false;
        }

        return $this->selected->valid();
    }

    public function createItem(): MenuItem {
        $this->item
            ->setContext($this)
            ->setMenuLevel($this->level);

        return $this->item;
    }

    public function createSubMenu(MenuItem $child): ?static {
        $menu = new static($child);

        $menu->root = $this->root ?? $this;
        $menu->level = $this->level + 1;
        $menu->path = Path::merge($this->path, $child->getMenuSegment());

        if ($this->isSelected($child->getLabel())) {
            $menu->selected = $this->selected;
            $this->selected = null;

            $menu->selected->next();
            $menu->setIsExpanded(true);
        }

        return $menu;
    }
}
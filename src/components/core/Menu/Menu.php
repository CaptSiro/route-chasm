<?php

namespace components\core\Menu;

use components\core\Menu\Item\MenuItem;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\route\Path;
use core\translation\UrlPathTranslator;
use core\url\UrlGraph;
use core\view\Renderer;
use core\view\View;

class Menu implements View, Attribute {
    use Renderer, HtmlAttribute;

    protected int $level = 0;
    protected bool $isInset = true;
    protected bool $isExpanded = false;
    protected ?Menu $root = null;
    protected UrlPathTranslator $paths;
    protected UrlGraph $graph;
    protected ?Path $selected = null;



    public function __construct(
        protected string $path = '',
        protected ?MenuItem $itemTemplate = null
    ) {
        $this->paths = new UrlPathTranslator();
        $this->graph = new UrlGraph(
            $this->paths->getSegments()
        );

        $this->root = $this;

        if (is_null($this->itemTemplate)) {
            $this->itemTemplate = new MenuItem();
        }
    }



    public function getPath(): string {
        return $this->path;
    }

    public function getRootMenu(): Menu {
        return $this->root;
    }

    public function getStateClasses(): string {
        $classes = '';

        if ($this->hasItem()) {
            $classes .= ' has-item';
        }

        if (!$this->isEmpty()) {
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

    public function add(string $path, mixed $item): static {
        $this->graph->add($path, $item);
        return $this;
    }

    public function translatePathToTarget(string $path): array {
        return $this->paths->getTarget($path);
    }

    public function getGraph(): UrlGraph {
        return $this->graph;
    }

    public function isEmpty(): bool {
        $keys = array_keys($this->graph->getRoot());
        if (empty($keys)) {
            return true;
        }

        if (count($keys) === 1 && $keys[0] === UrlGraph::KEY_LEAF) {
            return true;
        }

        return false;
    }

    public function hasItem(): bool {
        return $this->graph->hasItem();
    }

    public function getItem(): mixed {
        return $this->graph->getRootItem();
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

    public function createItem(string $target): MenuItem {
        return $this->itemTemplate
            ->setContext($this)
            ->setPath($this->path)
            ->setHasValue($this->hasItem())
            ->setValue($this->getItem())
            ->setLabel($this->paths->getSegments()->getSource($target));
    }

    public function createSubMenu(string $target): ?static {
        $subGraph = $this->graph->getSubGraph($target);
        if (is_null($subGraph)) {
            return null;
        }

        $menu = new static(
            Path::join($this->path, $target),
            $this->itemTemplate
        );

        $menu->root = $this->root;

        $menu->graph = $subGraph;
        $menu->paths = $this->paths;
        $menu->level = $this->level + 1;

        if ($this->isSelected($target)) {
            $menu->selected = $this->selected;
            $this->selected = null;

            $menu->selected->next();
            $menu->setIsExpanded(true);
        }

        return $menu;
    }
}
<?php

namespace components\layout\PageMenu;

use components\layout\Menu\Menu;
use components\layout\Menu\MenuItem;
use components\layout\Menu\MenuItemContext;
use core\locale\LexiconUnit;
use core\route\Path;
use core\view\ViewTemplateRenderer;
use models\Page\Page;

class PageMenuItem implements MenuItem {
    use ViewTemplateRenderer, MenuItemContext, LexiconUnit;



    protected array $children = [];
    protected Page $page;
    protected Path $segment;
    protected ?Path $binding = null;

    public function __construct(
        protected string $title
    ) {
        $this->setLexiconGroup(Menu::LEXICON_GROUP);
        $this->segment = Path::from($title);
    }



    public function hasChildren(): bool {
        return !empty($this->children);
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function getChild(string $title, bool $create = false): ?static {
        $child = $this->children[$title] ?? null;
        if (!is_null($child)) {
            return $child;
        }

        if ($create) {
            $this->children[$title] = $child = new static($title);
        }

        return $child;
    }

    public function setItem(Page $page): static {
        $this->page = $page;
        return $this;
    }

    public function hasItem(): bool {
        return isset($this->page);
    }

    public function createUrl(): string {
        if (!isset($this->page)) {
            return '#';
        }

        return $this->page->getUrl();
    }

    public function getLabel(): string {
        return $this->title ?? "";
    }

    public function getMenuSegment(): Path {
        return $this->segment;
    }

    public function getStateClasses(): string {
        return '';
    }
}
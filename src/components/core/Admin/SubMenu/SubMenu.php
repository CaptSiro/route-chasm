<?php

namespace components\core\Admin\SubMenu;

use core\AdminRouter;
use core\App;
use core\html\HtmlAttribute;
use core\html\Attribute;
use core\path\Path;
use core\translation\Translator;
use core\url\UrlGraph;
use core\view\Renderer;
use core\view\View;

class SubMenu implements View, Attribute {
    use Renderer, HtmlAttribute;



    protected bool $inset = true;

    public function __construct(
        protected Translator $segments,
        protected string $path,
        protected array $menu,
        protected bool $isExpanded = true,
        protected ?Path $selected = null
    ) {
        if ($this->hasRender()) {
            $this->addCssClass('has-target');
        }

        if (!$this->isEmpty()) {
            $this->addCssClass('has-sub-menu');
        }

        if ($this->isLeaf()) {
            $this->addCssClass('selected');
        }
    }



    public function isEmpty(): bool {
        $keys = array_keys($this->menu);
        if (empty($keys)) {
            return true;
        }

        if (count($keys) === 1 && $keys[0] === UrlGraph::KEY_LEAF) {
            return true;
        }

        return false;
    }

    public function hasRender(?string $target = null): bool {
        if (is_null($target)) {
            return UrlGraph::isLeaf($this->menu);
        }

        return UrlGraph::isLeaf($this->menu[$target]);
    }

    public function createItemUrl(string $target = ''): string {
        return App::getInstance()
            ->prependHome(AdminRouter::getInstance()->getPath() . $this->path .'/'. $target);
    }

    public function inset(bool $bool): static {
        $this->inset = $bool;
        return $this;
    }

    public function isSelected(string $target): bool {
        if (is_null($this->selected) || $this->selected->isExhausted()) {
            return false;
        }

        $current = $this->selected->current();
        return $current->test($target, $ignored);
    }

    public function isLeaf(): bool {
        if (is_null($this->selected)) {
            return false;
        }

        return $this->selected->isExhausted();
    }

    public function createSubMenu(string $target): static {
        $isSelected = $this->isSelected($target);
        if ($isSelected) {
            $this->selected->next();
        }

        return new static(
            $this->segments,
            $this->path .'/'. $target,
            $this->menu[$target],
            $isSelected,
            $isSelected ? $this->selected : null
        );
    }
}
<?php

namespace components\core\Admin\SubMenu;

use core\AdminRouter;
use core\App;
use core\html\HtmlAttribute;
use core\html\Attribute;
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
        protected array $menu
    ) {}



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

    public function createSubMenu(string $target): static {
        return new static($this->segments, $this->path .'/'. $target, $this->menu[$target]);
    }
}
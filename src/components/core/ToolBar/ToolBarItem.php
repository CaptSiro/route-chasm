<?php

namespace components\core\ToolBar;

use components\core\Menu\MenuItem;
use components\core\Menu\MenuItemContext;
use core\collections\graph\Edge;
use core\collections\graph\TreeVertex;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\RouteSegment;
use core\view\Renderer;

class ToolBarItem implements MenuItem, Attribute {
    use Renderer, MenuItemContext, LexiconUnit, HtmlAttribute;

    public const LEXICON_GROUP = 'tool-bar';



    public static function createVertex(): TreeVertex {
        $item = new static("todo");
        return $item->vertex;
    }



    /** @var TreeVertex<ToolBarItem, RouteSegment> */
    protected TreeVertex $vertex;

    /**
     * @param string $action
     * @param string|null $shortcut
     */
    public function __construct(
        protected string $action,
        protected ?string $shortcut = null,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->vertex = new TreeVertex($this);
    }



    /**
     * @return TreeVertex<ToolBarItem, RouteSegment>
     */
    public function getVertex(): TreeVertex {
        return $this->vertex;
    }

    /**
     * @param TreeVertex<ToolBarItem, RouteSegment> $vertex
     */
    public function setVertex(TreeVertex $vertex): static {
        $this->vertex = $vertex;
        return $this;
    }

    public function getAction(): string {
        return $this->action;
    }

    public function setAction(string $action): static {
        $this->action = $action;
        return $this;
    }

    public function getShortcut(): ?string {
        return $this->shortcut;
    }

    public function setShortcut(?string $shortcut): static {
        $this->shortcut = $shortcut;
        return $this;
    }



    // MenuItem
    public function hasItem(): bool {
        return !empty($this->action);
    }

    public function hasChildren(): bool {
        return !empty($this->vertex->getEdges());
    }

    public function getChildren(): array {
        return array_map(
            fn(Edge $x) => $x->getVertex()->get(),
            $this->vertex->getEdges()
        );
    }

    public function getLabel(): string {
        if (is_null($edge = $this->vertex->getParentEdge())) {
            return '';
        }

        return $edge->get()->getLabel();
    }

    public function getMenuSegment(): Path {
        if (is_null($edge = $this->vertex->getParentEdge())) {
            return Path::empty();
        }

        return Path::from($edge->get()->getSource());
    }

    public function getStateClasses(): string {
        $classes = '';

        if ($this->getMenuLevel() <= 0) {
            $classes .= ' menu-item-top';
        }

        if ($this->hasChildren()) {
            $classes .= ' inner-dropdown';
        }

        return $classes;
    }
}
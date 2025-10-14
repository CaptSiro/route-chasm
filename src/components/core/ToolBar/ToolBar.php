<?php

namespace components\core\ToolBar;

use components\core\Menu\Menu;
use core\collections\graph\Edge;
use core\collections\graph\Graph;
use core\collections\graph\TreeVertex;
use core\collections\graph\Vertex;
use core\route\Route;
use core\route\RouteExtend;
use core\view\Renderer;
use core\view\View;

class ToolBar implements Graph, View {
    use Renderer;



    protected RouteExtend $extend;
    protected TreeVertex $root;
    protected Menu $menu;

    public function __construct(
        ?TreeVertex $root = null,
        ?Graph $graph = null
    ) {
        $this->root = $root ?? ToolBarItem::createVertex();
        $this->extend = new RouteExtend($graph ?? $this);
        $this->menu = new Menu($this->root->get());
        $this->menu->setTemplate($this->getResource('ToolBar.menu.phtml'));
    }



    public function add(Route $route, ToolBarItem $item): static {
        $vertex = $this->extend->trace($this->root, $route);
        $vertex->set($item->setVertex($vertex));
        return $this;
    }



    // Graph<ToolBarItem, RouteSegment>
    public function createVertex(): Vertex {
        return ToolBarItem::createVertex();
    }

    public function createEdge(mixed $edge, Vertex $vertex): Edge {
        return new Edge($edge, $vertex);
    }
}
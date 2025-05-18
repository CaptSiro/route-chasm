<?php

namespace core\route;

use core\collection\graph\TreeVertex;
use core\path\Path;

class RouteTree {
    /** @var TreeVertex<RouteNode, string> */
    protected TreeVertex $root;

    /** @var RouteSearch<RouteNode, string> */
    protected RouteSearch $search;

    /** @var RouteExtend<RouteNode, string> */
    protected RouteExtend $routeExtend;

    /**
     * @param ?TreeVertex<RouteNode, string> $root
     */
    public function __construct(
        ?TreeVertex $root = null
    ) {
        $this->root = $root ?? new TreeVertex(new RouteNode());
        $this->search = new RouteSearch();
        $this->routeExtend = new RouteExtend(fn() => new TreeVertex(new RouteNode()));
    }



    /**
     * @return TreeVertex<RouteNode, string>
     */
    public function getRoot(): TreeVertex {
        return $this->root;
    }

    public function search(Path $path): null {
        $vertexes = $this->search->search($this->root, $path);
        return null;
    }

    /**
     * @param Route $route
     * @return RouteNode
     */
    public function getVertex(Route $route): RouteNode {
        $vertex = $this->routeExtend->trace($this->root, $route);
        return $vertex->getValue();
    }
}
<?php

namespace core\route;

use core\collections\graph\Edge;
use core\collections\graph\Graph;
use core\collections\graph\TreeVertex;
use core\collections\graph\Vertex;

/**
 * @template-implements Graph<RouteNode, RouteSegment>
 */
class RouteTree implements Graph {
    /**
     * @param TreeVertex<RouteNode, RouteSegment> $root
     * @param Path $path
     * @return array<TreeVertex<RouteNode, RouteSegment>>
     */
    public static function find(TreeVertex $root, Path $path): array {
        if ($path->getDepth() === 0) {
            return [$root];
        }

        /** @var array<TreeVertex<RouteNode, RouteSegment>> $layer */
        $layer = [$root];

        /** @var array<TreeVertex<RouteNode, RouteSegment>> $layerNext */
        $layerNext = [];

        /** @var array<TreeVertex<RouteNode, RouteSegment>> $terminal */
        $terminal = [];

        $maxDepth = $path->getDepth() - 1;

        foreach ($path->getSegments() as $i => $segment) {
            if (empty($layer)) {
                break;
            }

            foreach ($layer as $vertex) {
                $edges = $vertex->getEdges();

                foreach ($edges as $edge) {
                    if ($edge->get()->test($segment)) {
                        $layerNext[] = $edge->getVertex();
                    }
                }

                if (empty($edges) || $vertex->get()->hasFlag(RouteNode::FLAG_IS_TERMINAL)) {
                    $terminal[] = $vertex;
                }
            }

            $layer = $layerNext;
            $layerNext = [];
        }

        return array_merge($terminal, $layer);
    }

    /**
     * @param TreeVertex<RouteNode, RouteSegment> $root
     * @return array<RouteSegment>
     */
    public static function getRouteSegments(TreeVertex $root): array {
        $current = $root;
        $ret = [];

        while (true) {
            $edge = $current->getParentEdge();
            if (is_null($edge)) {
                return array_reverse($ret);
            }

            $ret[] = $edge->get();
            $current = $edge->getVertex();
        }
    }



    /** @var TreeVertex<RouteNode, RouteSegment> */
    protected TreeVertex $root;

    /** @var RouteExtend<TreeVertex<RouteNode, RouteSegment>> */
    protected RouteExtend $routeExtend;



    /**
     * @param ?TreeVertex<RouteNode, RouteSegment> $root
     */
    public function __construct(
        ?TreeVertex $root = null,
        ?RouteExtend $extend = null,
    ) {
        $this->root = $root ?? RouteNode::createEmpty();
        $this->routeExtend = $extend ?? new RouteExtend($this);
    }



    /**
     * @return TreeVertex<RouteNode, RouteSegment>
     */
    public function getRoot(): TreeVertex {
        return $this->root;
    }

    /**
     * @param TreeVertex<RouteNode, RouteSegment> $root
     */
    public function setRoot(TreeVertex $root): void {
        $this->root = $root;
    }

    public function getRoute(): Route {
        return Route::fromSegments(self::getRouteSegments($this->root));
    }

    /**
     * @param Path $path
     * @return array<TreeVertex<RouteNode, RouteSegment>>
     */
    public function search(Path $path): array {
        return self::find($this->root, $path);
    }

    /**
     * @param Path $path
     * @return array<Trace<RouteNode, RouteSegment>>
     */
    public function traceSearch(Path $path): array {
        $vertexes = $this->search($path);
        if (empty($vertexes)) {
            return $vertexes;
        }

        // Create traces from found vertexes to tree root
        foreach (array_keys($vertexes) as $key) {
            $vertexes[$key] = Trace::backtrack($this->root, $vertexes[$key]);
        }

        // Descending order
        usort($vertexes, fn(Trace $a, Trace $b) => $b->getDepth() <=> $a->getDepth());
        return $vertexes;
    }

    /**
     * @param Route $route
     * @return RouteNode
     */
    public function getNode(Route $route): RouteNode {
        $vertex = $this->routeExtend->trace($this->root, $route);
        return $vertex->get();
    }

    /**
     * @param Route $route
     * @return TreeVertex<RouteNode, RouteSegment>
     */
    public function getVertex(Route $route): TreeVertex {
        return $this->routeExtend->trace($this->root, $route);
    }

    public function getSubTree(Route $route): RouteTree {
        return new static(
            $this->getVertex($route),
            $this->routeExtend
        );
    }



    // Graph<RouteNode, RouteSegment>
    public function createVertex(): Vertex {
        return RouteNode::createEmpty();
    }

    /**
     * @param RouteSegment $edge
     * @param Vertex $vertex
     * @return Edge
     */
    public function createEdge(mixed $edge, Vertex $vertex): Edge {
        return new Edge($edge, $vertex);
    }
}
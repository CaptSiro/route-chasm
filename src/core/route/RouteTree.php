<?php

namespace core\route;

use core\collection\graph\Edge;
use core\collection\graph\Graph;
use core\collection\graph\TreeVertex;
use core\collection\graph\Vertex;

/**
 * @template-implements Graph<RouteNode, RouteSegment>
 */
class RouteTree implements Graph {
    /** @var TreeVertex<RouteNode, RouteSegment> */
    protected TreeVertex $root;

    /** @var RouteExtend<RouteNode, RouteSegment> */
    protected RouteExtend $routeExtend;



    /**
     * @param ?TreeVertex<RouteNode, RouteSegment> $root
     */
    public function __construct(
        ?TreeVertex $root = null
    ) {
        $this->root = $root ?? RouteNode::createEmpty();
        $this->routeExtend = new RouteExtend($this);
    }



    /**
     * @return TreeVertex<RouteNode, RouteSegment>
     */
    public function getRoot(): TreeVertex {
        return $this->root;
    }

    /**
     * @param TreeVertex<RouteNode, RouteSegment> $root
     * @param Path $path
     * @return array<TreeVertex<RouteNode, RouteSegment>>
     */
    public function search(TreeVertex $root, Path $path): array {
        if ($path->getDepth() === 0) {
            return [$root];
        }

        /** @var TreeVertex<RouteNode, RouteSegment>[] $layer */
        $layer = [$root];

        /** @var TreeVertex<RouteNode, RouteSegment>[] $layerNext */
        $layerNext = [];

        /** @var TreeVertex<RouteNode, RouteSegment>[] $terminal */
        $terminal = [];

        $maxDepth = $path->getDepth() - 1;

        foreach ($path->getSegments() as $i => $segment) {
            if (empty($layer)) {
                break;
            }

            foreach ($layer as $vertex) {
                $edges = $vertex->getEdges();

                if (empty($edges)) {
                    $terminal[] = $vertex;
                    continue;
                }

                foreach ($edges as $edge) {
                    if ($edge->get()->test($segment)) {
                        $layerNext[] = $edge->getVertex();
                    }
                }
            }

            $layer = $layerNext;
            $layerNext = [];
        }

        return array_merge($terminal, $layer);
    }

    /**
     * @param Path $path
     * @return Trace<RouteNode, RouteSegment>[]
     */
    public function traceSearch(Path $path): array {
        $vertexes = $this->search($this->root, $path);

        foreach (array_keys($vertexes) as $key) {
            $vertexes[$key] = Trace::backtrack($this->root, $vertexes[$key]);
        }

        // Descending order
        usort($vertexes, fn(Trace $a, Trace $b) => $b->getWeight() <=> $a->getWeight());
        return $vertexes;
    }

    /**
     * @param Route $route
     * @return RouteNode
     */
    public function getTerminalVertex(Route $route): RouteNode {
        $vertex = $this->routeExtend->trace($this->root, $route);
        return $vertex->get();
    }



    // Graph<RouteNode, RouteSegment>
    public function createVertex(): Vertex {
        return RouteNode::createEmpty();
    }

    public function createEdge(mixed $edge, Vertex $vertex): Edge {
        return new Edge($edge, $vertex);
    }
}
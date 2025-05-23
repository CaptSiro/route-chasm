<?php

namespace core\route;

use core\collection\graph\Graph;
use core\collection\graph\Vertex;

class RouteExtend {
    /**
     * @param Graph<RouteNode, RouteSegment> $graph
     */
    public function __construct(
        protected Graph $graph
    ) {}



    /**
     * @param Vertex<RouteNode, RouteSegment> $root
     * @param Route $route
     * @return Vertex<RouteNode, RouteSegment>
     */
    public function trace(Vertex $root, Route $route): Vertex {
        $current = $root;

        foreach ($route->getSegments() as $segment) {
            foreach ($current->getEdges() as $edge) {
                if ($edge->get()->getPattern() === $segment) {
                    $current = $edge->getVertex();
                    continue 2;
                }
            }

            $vertex = $this->graph->createVertex();
            $current->addEdge($this->graph->createEdge($segment, $vertex));
            $current = $vertex;
        }

        return $current;
    }
}
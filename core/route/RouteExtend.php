<?php

namespace core\route;

use core\collections\graph\Graph;
use core\collections\graph\Vertex;

/**
 * @template-covariant T
 */
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
     * @return T
     */
    public function trace(Vertex $root, Route $route): Vertex {
        $current = $root;

        foreach ($route->getSegments() as $segment) {
            if ($segment->hasFlag(RouteSegment::FLAG_IS_TERMINAL)) {
                $current->get()->setFlag(RouteSegment::FLAG_IS_TERMINAL);
                break;
            }

            foreach ($current->getEdges() as $edge) {
                if ($edge->get()->getPattern() === $segment->getPattern()) {
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
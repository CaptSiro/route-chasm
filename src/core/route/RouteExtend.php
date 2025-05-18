<?php

namespace core\route;

use core\collection\graph\Vertex;

/**
 * @template V
 * @template E
 */
class RouteExtend {
    /**
     * @var callable(): Vertex<V, E>
     */
    protected $createVertex;

    /**
     * @param callable(): Vertex<V, E> $createVertex
     */
    public function __construct(callable $createVertex) {
        $this->createVertex = $createVertex;
    }



    /**
     * @param Vertex<RouteNode, string> $root
     * @param Route $route
     * @return Vertex
     */
    public function trace(Vertex $root, Route $route): Vertex {
        $current = $root;
        $depth = $route->getDepth();

        for ($i = 0; $i < $depth; $i++) {
            $segment = Route::createSegmentRegex($route->getSegment($i));

            foreach ($current->getEdges() as $edge) {
                if ($edge->getValue() === $segment) {
                    $current = $edge->getVertex();
                    continue 2;
                }
            }

            $vertex = ($this->createVertex)();
            $current->connect($segment, $vertex);
            $current = $vertex;
        }

        return $current;
    }
}
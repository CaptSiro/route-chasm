<?php

namespace core\collections\graph;

/**
 * @template V
 * @template E
 */
interface Graph {
    /**
     * @return Vertex<V, E>
     */
    public function createVertex(): Vertex;

    /**
     * @param E $edge
     * @param Vertex<V, E> $vertex
     * @return Edge<V, E>
     */
    public function createEdge(mixed $edge, Vertex $vertex): Edge;
}
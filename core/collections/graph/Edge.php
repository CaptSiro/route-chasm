<?php

namespace core\collections\graph;

/**
 * @template V
 * @template E
 */
class Edge {
    /**
     * @param E $value
     * @param Vertex<V> $vertex
     */
    public function __construct(
        protected mixed $value,
        protected Vertex $vertex
    ) {}



    /**
     * @return E
     */
    public function get(): mixed {
        return $this->value;
    }

    /**
     * @return Vertex<V, E>
     */
    public function getVertex(): Vertex {
        return $this->vertex;
    }

    /**
     * @param Vertex<V, E> $vertex
     */
    public function setVertex(Vertex $vertex): void {
        $this->vertex = $vertex;
    }
}
<?php

namespace core\collection\graph;

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
     * @return Vertex<V, E>
     */
    public function getVertex(): Vertex {
        return $this->vertex;
    }

    /**
     * @return E
     */
    public function getValue(): mixed {
        return $this->value;
    }
}
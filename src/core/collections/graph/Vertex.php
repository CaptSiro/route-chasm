<?php

namespace core\collections\graph;

/**
 * @template V
 * @template E
 */
class Vertex {
    /**
     * @var array<Edge<V, E>> $edges
     */
    protected array $edges;



    /**
     * @param V $value
     */
    public function __construct(
        protected mixed $value
    ) {
        $this->edges = [];
    }



    /**
     * @return V
     */
    public function get(): mixed {
        return $this->value;
    }

    /**
     * @param V $value
     */
    public function set(mixed $value): void {
        $this->value = $value;
    }

    /**
     * @return array<Edge<V, E>>
     */
    public function getEdges(): array {
        return $this->edges;
    }

    /**
     * @param Edge<V, E> $edge
     * @return void
     */
    public function addEdge(Edge $edge): void {
        $this->edges[] = $edge;
    }

    /**
     * @param E $edge
     * @param static<V, E> $vertex
     * @return void
     */
    public function connect(mixed $edge, Vertex $vertex): void {
        $this->addEdge(new Edge($edge, $vertex));
    }
}
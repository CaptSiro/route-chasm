<?php

namespace core\collection\graph;

/**
 * @template V
 * @template E
 * @template-extends Vertex<V, E>
 */
class TreeVertex extends Vertex {
    /** @var ?Edge<V, E> */
    protected ?Edge $parent;



    /**
     * @param E $value
     * @param static<V, E> $parent
     * @return void
     */
    protected function setParentEdge(mixed $value, Vertex $parent): void {
        $this->parent = new Edge($value, $parent);
    }

    /**
     * @return ?Edge<V, E>
     */
    public function getParent(): ?Edge {
        return $this->parent;
    }

    /**
     * @param Edge<V, E> $edge
     * @return void
     */
    public function addEdge(Edge $edge): void {
        $edge->getVertex()->setParentEdge($edge->getValue(), $this);
        parent::addEdge($edge);
    }
}
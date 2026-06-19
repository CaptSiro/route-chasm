<?php

namespace core\collections\graph;

use core\InstanceCounter;

/**
 * @template V
 * @template E
 * @template-extends Vertex<V, E>
 */
class TreeVertex extends Vertex {
    use InstanceCounter;

    /** @var ?Edge<V, E> */
    protected ?Edge $parent;



    public function __construct(mixed $value) {
        parent::__construct($value);
        $this->parent = null;
        $this->instanceId = self::createInstanceId();
    }



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
    public function getParentEdge(): ?Edge {
        return $this->parent;
    }

    /**
     * @return ?Vertex<V, E>
     */
    public function getParentVertex(): ?Vertex {
        if (is_null($this->parent)) {
            return null;
        }

        return $this->parent->getVertex();
    }

    /**
     * @param Edge<V, E> $edge
     * @return void
     */
    public function addEdge(Edge $edge): void {
        $edge->getVertex()->setParentEdge($edge->get(), $this);
        parent::addEdge($edge);
    }

    public function depth(): int {
        $depth = 0;
        $current = $this;

        while (true) {
            if (is_null($edge = $current->getParentEdge())) {
                break;
            }

            $current = $edge->getVertex();
            $depth++;
        }

        return $depth;
    }
}
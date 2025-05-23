<?php

namespace core\route;

use core\collection\graph\TreeVertex;

/**
 * @template V
 * @template E
 */
class Trace {
    /**
     * @param TreeVertex<V, E> $root
     * @param TreeVertex<V, E> $target
     * @return self
     */
    public static function backtrack(TreeVertex $root, TreeVertex $target): self {
        $trace = [$target];
        $current = $target;

        while ($current->getInstanceId() !== $root->getInstanceId()) {
            $edge = $current->getParentEdge();
            if (is_null($edge)) {
                break;
            }

            $current = $edge->getVertex();
            $trace[] = $current;
        }

        return new self(array_reverse($trace));
    }



    /**
     * @param array<TreeVertex<V, E>> $vertexes
     */
    public function __construct(
        protected array $vertexes
    ) {}



    /**
     * @return array<TreeVertex<V, E>>
     */
    public function getVertexes(): array {
        return $this->vertexes;
    }
}
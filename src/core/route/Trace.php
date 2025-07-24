<?php

namespace core\route;

use core\collections\graph\TreeVertex;
use core\collections\graph\WeightedEdge;
use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;

/**
 * @template V
 * @template E
 * @template-implements ArrayIterator<TreeVertex<V, E>>
 */
class Trace implements ArrayIterator {
    use ArrayIteratorTrait;

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
     * @param array<TreeVertex<V, WeightedEdge>> $vertexes
     */
    public function __construct(
        protected array $vertexes,
    ) {}



    /**
     * @return array<TreeVertex<V, E>>
     */
    public function getVertexes(): array {
        return $this->vertexes;
    }

    public function getDepth(): int {
        return count($this->vertexes);
    }



    // ArrayIterator
    public function getArrayIterator(): array {
        return $this->vertexes;
    }

    public function key(): int {
        return $this->arrayIteratorIndex;
    }
}
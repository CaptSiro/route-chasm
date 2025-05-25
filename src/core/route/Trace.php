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
     * @param TreeVertex<V, WeightedEdge> $root
     * @param TreeVertex<V, WeightedEdge> $target
     * @return self
     */
    public static function backtrack(TreeVertex $root, TreeVertex $target): self {
        $trace = [$target];
        $current = $target;
        $weight = 0;

        while ($current->getInstanceId() !== $root->getInstanceId()) {
            $edge = $current->getParentEdge();
            if (is_null($edge)) {
                break;
            }

            $weight += $edge->get()->getWeight();
            $current = $edge->getVertex();
            $trace[] = $current;
        }

        return new self(array_reverse($trace), $weight);
    }



    /**
     * @param array<TreeVertex<V, WeightedEdge>> $vertexes
     */
    public function __construct(
        protected array $vertexes,
        protected ?float $weight = null
    ) {
        $this->weight ??= $this->calculateWeight();
    }



    protected function calculateWeight(): float {
        $weight = 0;

        for ($i = 1; $i < count($this->vertexes); $i++) {
            $weight += $this->vertexes[$i]->getParentEdge()?->get()->getWeight() ?? 1;
        }

        return $weight;
    }

    /**
     * @return array<TreeVertex<V, E>>
     */
    public function getVertexes(): array {
        return $this->vertexes;
    }

    public function getWeight(): float {
        return $this->weight;
    }



    // ArrayIterator
    public function arrayIterator(): array {
        return $this->vertexes;
    }

    public function key(): int {
        return $this->arrayIterator;
    }
}
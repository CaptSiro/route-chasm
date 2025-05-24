<?php

namespace core\route;

use core\collection\graph\TreeVertex;
use core\collection\graph\WeightedEdge;

/**
 * @template V
 * @template E
 */
class Trace {
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
}
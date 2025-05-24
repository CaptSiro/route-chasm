<?php

namespace core\collection\graph;

interface WeightedEdge {
    public function getWeight(): float;
}
<?php

namespace core\collections\graph;

interface WeightedEdge {
    public function getWeight(): float;
}
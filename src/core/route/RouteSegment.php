<?php

namespace core\route;

use core\collection\graph\WeightedEdge;
use core\route\compiler\RouteCompilerOptions;
use core\utils\Regex;

class RouteSegment implements WeightedEdge {
    protected string $regex;

    public function __construct(
        protected string $pattern,
        protected float $weight = RouteCompilerOptions::WEIGHT_DEFAULT
    ) {
        $this->regex = Regex::create($this->pattern);
    }

    public function __toString(): string {
        return $this->pattern;
    }



    public function test($literal): bool {
        return preg_match($this->regex, $literal);
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getRegex(): string {
        return $this->regex;
    }

    public function getWeight(): float {
        return $this->weight;
    }
}
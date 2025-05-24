<?php

namespace core\route;

use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;
use core\route\compiler\RouteCompiler;

/**
 * @template-implements ArrayIterator<int, string>
 */
class Route implements ArrayIterator {
    use ArrayIteratorTrait;

    /**
     * @param string $route
     * @param array<string, string> $parameters
     * @return static
     */
    public static function from(string $route, array $parameters = []): static {
        // todo
        // get app Route compiler
        $parser = new RouteCompiler();
        return $parser->parse($route, $parameters);
    }



    /**
     * @var array<RouteSegment>
     */
    protected array $segments;



    public function __construct() {
        $this->segments = [];
    }

    public function __toString(): string {
        return '/'. implode('/', $this->segments);
    }



    public function add(RouteSegment $segment): void {
        $this->segments[] = $segment;
    }

    public function getDepth(): int {
        return count($this->segments);
    }

    /**
     * @return array<RouteSegment>
     */
    public function getSegments(): array {
        return $this->segments;
    }



    // ArrayIterator
    public function arrayIterator(): array {
        return $this->segments;
    }

    public function key(): int {
        return $this->arrayIterator;
    }
}
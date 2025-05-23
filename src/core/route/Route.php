<?php

namespace core\route;

use core\collection\iterator\ArrayIterator;
use core\collection\iterator\ArrayIteratorTrait;
use core\route\parser\RouteParser;

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
        // get app Route parser
        $parser = new RouteParser();
        return $parser->parse($route, $parameters);
    }



    /**
     * @var string[]
     */
    protected array $segments;



    public function __construct() {
        $this->segments = [];
    }

    public function __toString(): string {
        return '/'. implode('/', $this->segments);
    }



    public function add(string $segment): void {
        $this->segments[] = $segment;
    }

    public function getDepth(): int {
        return count($this->segments);
    }

    /**
     * @return array<string>
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
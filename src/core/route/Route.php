<?php

namespace core\route;

use core\collection\iterator\ArrayIterator;
use core\collection\iterator\ArrayIteratorTrait;

/**
 * @template-implements ArrayIterator<int, string>
 */
class Route implements ArrayIterator {
    use ArrayIteratorTrait;

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



    // ArrayIterator
    public function arrayIterator(): array {
        return $this->segments;
    }

    public function key(): int {
        return $this->arrayIterator;
    }
}
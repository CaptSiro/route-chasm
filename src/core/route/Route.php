<?php

namespace core\route;

use core\App;
use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;
use core\configs\AppConfig;
use core\Copy;
use core\route\compiler\RouteCompiler;
use core\utils\Arrays;

/**
 * @template-implements ArrayIterator<int, string>
 */
class Route implements ArrayIterator, Copy {
    use ArrayIteratorTrait;

    /**
     * @param string $route
     * @param array<string, string> $parameters
     * @return static
     */
    public static function from(string $route, array $parameters = []): static {
        $parser = App::getInstance()
            ->getRouteCompiler();

        return $parser->parse($route, $parameters);
    }

    public static function resolve(Route|string $route): static {
        if ($route instanceof Route) {
            return $route;
        }

        return self::from($route);
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

    public function extend(self $route): static {
        $this->segments = array_merge($this->segments, $route->segments);
        return $this;
    }



    // ArrayIterator
    public function arrayIterator(): array {
        return $this->segments;
    }

    public function key(): int {
        return $this->arrayIterator;
    }

    // Copy
    public function copy(): static {
        $copy = new static();
        $copy->segments = Arrays::copy($this->segments);
        return $copy;
    }
}
<?php

namespace core\route;

use core\App;
use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;
use core\Copy;
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
        $compiler = App::getInstance()
            ->getRouteCompiler();

        return $compiler->parse($route, $parameters);
    }

    public static function format(string $route, array $parameters = []): Path {
        $compiler = App::getInstance()
            ->getRouteCompiler();

        return $compiler->format($route, $parameters);
    }

    public static function isDynamic(string $route): bool {
        $compiler = App::getInstance()
            ->getRouteCompiler();

        return $compiler->isDynamic($route);
    }

    /**
     * @param array<RouteSegment> $segments
     * @return static
     */
    public static function fromSegments(array $segments): static {
        $route = new static(RouteSegment::source($segments));
        $route->segments = $segments;
        return $route;
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



    public function __construct(
        protected string $source
    ) {
        $this->segments = [];
    }

    public function __toString(): string {
        return '/'. implode('/', $this->segments);
    }



    public function add(RouteSegment $segment): void {
        $this->segments[] = $segment;
    }

    public function getSource(): string {
        return $this->source;
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

    public function hasDynamicBehaviour(): bool {
        return self::isDynamic($this->source);
    }

    public function toPath(array $parameters = []): Path {
        return self::format($this->source, $parameters);
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
        $copy = new static($this->source);
        $copy->segments = Arrays::copy($this->segments);
        return $copy;
    }
}
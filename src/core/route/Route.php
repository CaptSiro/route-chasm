<?php

namespace core\route;

use core\App;
use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;
use core\Copy;
use core\utils\Arrays;
use core\utils\Strings;

/**
 * @template-implements ArrayIterator<int, RouteSegment>
 */
class Route implements ArrayIterator, Copy {
    use ArrayIteratorTrait;



    public const MENU_ROUTE = "menu-route";



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

    public static function menu(string $labeledRoute): Route {
        $labels = Arrays::explode('/', $labeledRoute);
        $route = Route::from(implode(
            '/',
            array_map(fn($x) => Strings::identifier($x), $labels)
        ));

        foreach ($route->getSegments() as $i => $segment) {
            $segment->setLabel($labels[$i]);
            $segment->setMetadata(self::MENU_ROUTE, true);
        }

        return $route;
    }

    public static function isMenu(RouteSegment $segment): bool {
        return $segment->getMetadata(self::MENU_ROUTE) ?? false;
    }

    public static function format(string $route, array $parameters = []): Path {
        if (empty($parameters)) {
            return Path::from($route);
        }

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
     * @var array<RouteSegment>
     */
    protected array $segments;



    public function __construct(
        protected string $source = ''
    ) {
        $this->segments = [];
    }

    public function __toString(): string {
        return '/'. implode('/', $this->segments);
    }



    public function get(string $segment): ?RouteSegment {
        foreach ($this->segments as $x) {
            if ($x->getLabel() === $segment || $x->getSource() === $segment) {
                return $x;
            }
        }

        return null;
    }

    public function add(RouteSegment $segment): void {
        $this->segments[] = $segment;
        $this->source = Path::join($this->source, $segment->getSource());
    }

    public function label(string $segment, ?string $label = null): static {
        if (!is_null($x = $this->get($segment))) {
            $x->setLabel($label);
        }

        return $this;
    }

    public function icon(string $segment, ?string $icon = null): static {
        if (!is_null($x = $this->get($segment))) {
            $x->setIcon($icon);
        }

        return $this;
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

    public function toStaticPath(): Path {
        if ($this->hasDynamicBehaviour()) {
            throw new DynamicRouteException("Trying to format dynamic route as a static route: '$this'");
        }

        return self::format($this->source);
    }

    public function extend(self $route): static {
        $this->segments = array_merge($this->segments, $route->segments);
        return $this;
    }



    // ArrayIterator
    public function getArrayIterator(): array {
        return $this->segments;
    }

    public function key(): int {
        return $this->arrayIteratorIndex;
    }

    // Copy
    public function copy(): static {
        $copy = new static($this->source);
        $copy->segments = Arrays::copy($this->segments);
        return $copy;
    }
}
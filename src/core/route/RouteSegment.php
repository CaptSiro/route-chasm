<?php

namespace core\route;

use core\collections\dictionary\StrictStack;
use core\Copy;
use core\Flags;
use core\utils\Regex;

class RouteSegment implements Copy {
    use Flags;

    public const FLAG_IS_TERMINAL = 1;

    /**
     * @param array<RouteSegment> $segments
     * @return string
     */
    public static function source(array $segments): string {
        return '/'. implode('/', array_map(fn(RouteSegment $x) => $x->getSource(), $segments));
    }



    protected string $regex;

    public function __construct(
        protected string $source,
        protected string $pattern
    ) {
        $this->regex = Regex::create($this->pattern);
    }

    public function __toString(): string {
        return $this->pattern;
    }



    public function test(string $literal): bool {
        return preg_match($this->regex, $literal);
    }

    public function match(string $literal, StrictStack $parameters): void {
        $groups = [];

        if (preg_match($this->regex, $literal, $groups)) {
            $parameters->push($groups);
        }
    }

    public function getSource(): string {
        return $this->source;
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getRegex(): string {
        return $this->regex;
    }



    // Copy
    public function copy(): static {
        return new static(
            $this->source,
            $this->pattern
        );
    }
}
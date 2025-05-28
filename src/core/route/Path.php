<?php

namespace core\route;

class Path {
    public static function from(string $literal, int $start = 0): self {
        $segments = [];

        foreach (explode('/', $literal) as $segment) {
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return new self($segments, $start);
    }



    /**
     * @param array<string> $segments
     */
    public function __construct(
        protected array $segments,
        protected int $start = 0
    ) {}

    public function __toString(): string {
        return '/'. implode('/', array_slice($this->segments, $this->start));
    }



    public function getDepth(): int {
        return count($this->segments) - $this->start;
    }

    /**
     * @return array<string>
     */
    public function getSegments(): array {
        return $this->segments;
    }

    public function getSegment(int $index): ?string {
        return $this->segments[$this->start + $index] ?? null;
    }

    public function toString(): string {
        return (string) $this;
    }
}
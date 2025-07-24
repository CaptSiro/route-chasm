<?php

namespace core\route;

class Path {
    public static function from(string $literal, int $offset = 0): self {
        $segments = [];

        foreach (explode('/', $literal) as $segment) {
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return new self($segments, $offset);
    }



    /**
     * @param array<string> $segments
     */
    public function __construct(
        protected array $segments,
        protected int $offset = 0
    ) {}

    public function __toString(): string {
        return '/'. implode('/', $this->getSegments());
    }



    public function getOffset(): int {
        return $this->offset;
    }

    public function getDepth(): int {
        return count($this->segments) - $this->offset;
    }

    /**
     * @return array<string>
     */
    public function getSegments(): array {
        return array_slice($this->segments, $this->offset);
    }

    /**
     * @return array<string>
     */
    public function getAllSegments(): array {
        return $this->segments;
    }

    public function getSegment(int $index): ?string {
        return $this->segments[$this->offset + $index] ?? null;
    }

    public function toString(): string {
        return (string) $this;
    }
}
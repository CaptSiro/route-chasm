<?php

namespace core\route;

class Path {
    public static function from(string $literal): self {
        $segments = [];

        foreach (explode('/', $literal) as $segment) {
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return new self($segments);
    }



    /**
     * @param array<string> $segments
     */
    public function __construct(
        protected array $segments
    ) {}

    public function __toString(): string {
        return implode('/', $this->segments);
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

    public function getSegment(int $index): ?string {
        return $this->segments[$index] ?? null;
    }
}
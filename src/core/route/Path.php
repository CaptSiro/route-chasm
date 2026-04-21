<?php

namespace core\route;

use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;
use core\Copy;
use core\utils\Arrays;
use JsonSerializable;

/**
 * @template-implements ArrayIterator<int, string>
 */
class Path implements ArrayIterator, Copy, JsonSerializable {
    use ArrayIteratorTrait;



    public static function empty(): Path {
        return new self([], 0);
    }

    public static function resolve(Path|string $path): Path {
        return $path instanceof Path
            ? $path
            : Path::from($path);
    }

    public static function merge(Path|string ...$paths): Path {
        $segments = [];

        foreach (array_map(fn($x) => Path::resolve($x), $paths) as $path) {
            $segments = array_merge(
                $segments,
                $path->getSegments()
            );
        }

        return new self($segments);
    }

    public static function depth(string $literal): int {
        $literalLength = strlen($literal);
        if ($literalLength === 0) {
            return 0;
        }

        if ($literalLength === 1) {
            return intval($literal !== "/");
        }

        $start = intval($literal[0] === '/');
        $length = $literalLength - $start - intval($literal[$literalLength - 1] === '/');
        return 1 + substr_count($literal, '/', $start, $length);
    }

    public static function from(string $literal, int $offset = 0, string $separator = '/'): self {
        $segments = [];

        foreach (explode($separator, $literal) as $segment) {
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return new self($segments, $offset);
    }

    public static function join(string ...$segments): string {
        return self::joinArray($segments);
    }

    /**
     * @param array<string> $segments
     * @param string $separator
     * @return string
     */
    public static function joinArray(array $segments, string $separator = '/'): string {
        $segments = array_values(array_filter($segments, fn($x) => $x !== ''));

        if (empty($segments)) {
            return '';
        }

        $count = count($segments);
        if ($count === 1) {
            return $segments[0];
        }

        $start = array_shift($segments);
        $end = array_pop($segments);
        $count -= 2;

        if ($count === 0) {
            return rtrim($start, $separator)
                .$separator. ltrim($end, $separator);
        }

        for ($i = 0; $i < $count; $i++) {
            $segments[$i] = trim($segments[$i], $separator);
        }

        return rtrim($start, $separator)
            .$separator. implode($separator, $segments)
            .$separator. ltrim($end, $separator);
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

    public function setOffset(int $offset): static {
        if (count($this->segments) === 0) {
            $this->offset = 0;
            return $this;
        }

        $this->offset = max($offset, count($this->segments) - 1);
        return $this;
    }

    public function getDepth(): int {
        return count($this->segments) - $this->offset;
    }

    public function isEmpty(): bool {
        return $this->getDepth() === 0;
    }

    /**
     * @return array<string>
     */
    public function getSegments(): array {
        return array_slice($this->segments, $this->offset);
    }

    public function first(): ?string {
        return Arrays::first($this->segments);
    }

    public function last(): ?string {
        return Arrays::last($this->segments);
    }

    public function append(string $segment): static {
        $this->segments[] = $segment;
        return $this;
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

    public function toString(string $separator = '/', bool $prependSlash = true): string {
        $ret = implode($separator, $this->getSegments());

        if ($prependSlash) {
            return $separator . $ret;
        }

        return $ret;
    }



    // ArrayIterator
    public function getArrayIterator(): array {
        return $this->segments;
    }

    public function key(): int {
        return $this->arrayIteratorIndex;
    }

    public function rewind(): void {
        $this->arrayIteratorIndex = $this->offset;
    }



    // Copy
    public function copy(): static {
        return new static(Arrays::copy($this->segments), $this->offset);
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return $this->segments;
    }
}
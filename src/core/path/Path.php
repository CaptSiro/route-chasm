<?php

namespace core\path;

use core\DoesNotExistException;
use core\path\parser\Parser;
use core\patterns\Pattern;
use core\Pipeline;

class Path implements Pipeline {
    public static function join(string ...$segments): string {
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
            return rtrim($start, '/\\')
                .'/'. ltrim($end, '/\\');
        }

        for ($i = 0; $i < $count; $i++) {
            $segments[$i] = trim($segments[$i], '/\\');
        }

        return rtrim($start, '/\\')
            .'/'. implode('/', $segments)
            .'/'. ltrim($end, '/\\');
    }

    public static function from(Path|string $literal): self {
        return $literal instanceof Path
            ? $literal
            : Parser::parse($literal);
    }

    public static function fromRaw(array $segments): self {
        $p = new self();

        foreach ($segments as $parts) {
            $s = new Segment();

            foreach ($parts as $part) {
                $s->addPart(new Part(...$part));
            }

            $p->addSegment($s);
        }

        return $p;
    }

    public static function fromStringArray(array $literals): self {
        $path = new self();

        foreach ($literals as $literal) {
            $segment = new Segment();
            $segment->addPart(new Part(PartType::STATIC, $literal));
            $path->addSegment($segment);
        }

        return $path;
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

    public static function compare(Path $a, Path $b): bool {
        $segmentsA = $a->getSegments();
        $segmentsB = $b->getSegments();

        $count = count($segmentsA);

        if ($count !== count($segmentsB)) {
            return false;
        }

        for ($i = 0; $i < $count; $i++) {
            if (Segment::compare($segmentsA[$i], $segmentsB[$i]) === false) {
                return false;
            }
        }

        return true;
    }



    /** @var Segment[] $segments */
    protected array $segments;
    private int $index;



    public function __construct() {
        $this->segments = [];
        $this->index = 0;
    }



    public function getDepth(): int {
        return count($this->segments);
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array {
        return $this->segments;
    }

    public function addSegment(Segment $section): void {
        $this->segments[] = $section;
    }

    public function getParams(): array {
        $params = [];

        foreach ($this->segments as $segment) {
            $segment->getParams($params);
        }

        return $params;
    }

    public function param(string $name, Pattern $pattern): self {
        $exists = false;

        foreach ($this->segments as $segment) {
            $exists = $exists || $segment->setParam($name, $pattern);
        }

        if (!$exists) {
            throw new DoesNotExistException("Parameter [$name] is not present in path '$this'", $name);
        }

        return $this;
    }

    public function merge(Path|string $extension): self {
        $clone = $this->clone();
        $parsed = Path::from($extension);

        $clone->segments = array_merge($clone->segments, $parsed->segments);

        return $clone;
    }

    public function clone(): self {
        $path = Path::from("$this");

        foreach ($this->getParams() as $name => $part) {
            $path->param($name, clone $part->pattern);
        }

        return $path;
    }

    public function __toString(): string {
        return implode('/', $this->segments);
    }

    public function next(): ?Segment {
        return $this->segments[++$this->index] ?? null;
    }

    /**
     * @return Segment
     */
    public function current(): mixed {
        return $this->segments[$this->index];
    }

    public function isExhausted(): bool {
        return $this->index >= count($this->segments);
    }

    public function rewind(): void {
        $this->index = 0;
    }
}
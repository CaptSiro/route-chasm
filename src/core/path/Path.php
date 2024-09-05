<?php

namespace core\path;

use core\DoesNotExistException;
use core\path\parser\Parser;
use core\Pipeline;
use patterns\Pattern;

class Path implements Pipeline {
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
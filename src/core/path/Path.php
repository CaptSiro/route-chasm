<?php

namespace core\path;

use core\path\parser\Parser;
use patterns\Pattern;

class Path {
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
    private array $segments;
    /** @var array<string, Part> $params */
    private array $params;
    private int $index = 0;



    public function __construct() {
        $this->segments = [];
        $this->params = [];
    }



    /**
     * @return Segment[]
     */
    public function getSegments(): array {
        return $this->segments;
    }

    public function addSegment(Segment $section): void {
        $this->segments[] = $section;
        $section->getParams($this->params);
    }

    public function param(string $name, Pattern $pattern): self {
        foreach ($this->segments as $segment) {
            $segment->setParam($name, $pattern);
        }

        return $this;
    }

    public function hasNext(): bool {
        return isset($this->segments[$this->index]);
    }

    public function next(): Segment {
        $this->index++;
        return $this->segments[$this->index - 1];
    }

    public function rewind(): void {
        $this->index = 0;
    }



    public function __toString(): string {
        return implode('/', $this->segments);
    }
}
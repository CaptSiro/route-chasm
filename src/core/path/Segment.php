<?php

namespace core\path;

use Exception;
use patterns\Pattern;
use patterns\Stream;

class Segment {
    public const FIRST = -1;

    public static function next(array $segments, int $position): int {
        $count = count($segments);

        for ($i = $position + 1; $i < $count; $i++) {
            if ($segments[$i] !== "") {
                break;
            }
        }

        return $i;
    }

    public static function isLast(array $segments, int $position): bool {
        $count = count($segments);

        if ($position >= $count) {
            return true;
        }

        $right = $count - 1;
        for (; $right >= 0; $right--) {
            if ($segments[$right] !== "") {
                break;
            }
        }

        if ($position > $right) {
            return true;
        }

        return false;
    }

    public static function compare(Segment $a, Segment $b): bool {
        $partsA = $a->getParts();
        $partsB = $b->getParts();

        $count = count($partsA);

        if ($count !== count($partsB)) {
            return false;
        }

        for ($i = 0; $i < $count; $i++) {
            if (Part::compare($partsA[$i], $partsB[$i]) === false) {
                return false;
            }
        }

        return true;
    }



    /** @var Part[] $parts */
    private array $parts;



    public function __construct() {
        $this->parts = [];
    }



    /**
     * @return array
     */
    public function getParts(): array {
        return $this->parts;
    }

    public function getParams(array &$reducer): void {
        foreach ($this->parts as $part) {
            if (!($part->type === PartType::DYNAMIC)) {
                continue;
            }

            if (isset($reducer[$part->literal])) {
                throw new Exception("Multiple definitions of '". $part->literal ."' dynamic part");
            }

            $reducer[$part->literal] = $part;
        }
    }

    public function addPart(Part $part): void {
        $this->parts[] = $part;
    }

    public function setParam(string $name, Pattern $pattern): void {
        foreach ($this->parts as $part) {
            if ($part->type === PartType::DYNAMIC && $part->literal === $name) {
                $part->pattern = $pattern;
            }
        }
    }

    public function test(string $segment, ?array &$matches): bool {
        $matches = [];
        $count = count($this->parts);

        if ($count === 1 && $this->parts[0]->type === PartType::STATIC) {
            return $segment === $this->parts[0]->literal;
        }

        $stream = new Stream($segment);
        foreach ($this->parts as $part) {
            $match = "";
            $failed = !$part->pattern->matchPipeline($stream, $match);
            if ($failed) {
                return false;
            }

            if ($part->type === PartType::DYNAMIC) {
                $matches[$part->literal] = $match;
            }
        }

        return $stream->isExhausted();
    }

    public function __toString(): string {
        return implode('', $this->parts);
    }
}

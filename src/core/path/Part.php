<?php

namespace core\path;



use patterns\AnyString;
use patterns\Exact;
use patterns\Pattern;

class Part {
    static function compare(Part $a, Part $b): bool {
        return $a->type === $b->type
            && $a->literal === $b->literal
            && get_class($a->pattern) === get_class($b->pattern);
    }



    public Pattern $pattern;

    public function __construct(
        public readonly PartType $type,
        public readonly string $literal,
        ?Pattern $pattern = null
    ) {
        $this->pattern = match ($this->type) {
            PartType::STATIC => new Exact($this->literal),
            PartType::DYNAMIC => is_null($pattern)
                ? AnyString::getInstance()
                : $pattern,
        };
    }



    public function __toString(): string {
        return match ($this->type) {
            PartType::DYNAMIC => "[$this->literal]",
            PartType::STATIC => $this->literal
        };
    }
}
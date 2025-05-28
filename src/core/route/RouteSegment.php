<?php

namespace core\route;

use core\collections\dictionary\StrictStack;
use core\Flags;
use core\utils\Regex;

class RouteSegment {
    use Flags;

    public const FLAG_IS_TERMINAL = 1;



    protected string $regex;

    public function __construct(
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

    // todo
    //  - Change to interface Stack: push(item) pop()->item clear() getSize()
    public function match(string $literal, StrictStack $parameters): void {
        $groups = [];

        if (preg_match($this->regex, $literal, $groups)) {
            $parameters->push($groups);
        }
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getRegex(): string {
        return $this->regex;
    }
}
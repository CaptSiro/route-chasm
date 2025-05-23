<?php

namespace core\route;

class RouteSegment {
    public static function createRegex(string $pattern): string {
        return "/^$pattern$/";
    }



    protected string $regex;

    public function __construct(
        protected string $pattern
    ) {
        $this->regex = self::createRegex($this->pattern);
    }

    public function __toString(): string {
        return $this->pattern;
    }



    public function test($literal): bool {
        return preg_match($this->regex, $literal);
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getRegex(): string {
        return $this->regex;
    }
}
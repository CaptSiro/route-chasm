<?php

namespace core\route;

use core\collections\dictionary\StrictStack;
use core\Copy;
use core\Flags;
use core\Metadata;
use core\utils\Regex;

class RouteSegment implements Copy {
    use Flags, Metadata;

    public const FLAG_IS_TERMINAL = 1;

    /**
     * @param array<RouteSegment> $segments
     * @return string
     */
    public static function source(array $segments): string {
        return '/'. implode('/', array_map(fn(RouteSegment $x) => $x->getSource(), $segments));
    }

    public static function static(string $segment): self {
        return new self($segment, $segment);
    }



    protected string $regex;
    protected ?string $label = null;
    protected ?string $icon = null;

    public function __construct(
        protected string $source,
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

    public function match(string $literal, StrictStack $parameters): bool {
        $groups = [];

        if (!preg_match($this->regex, $literal, $groups)) {
            return false;
        }

        $parameters->push($groups);
        return true;
    }

    public function getSource(): string {
        return $this->source;
    }

    public function setSource(string $source): void {
        $this->source = $source;
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getRegex(): string {
        return $this->regex;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function setLabel(?string $label): void {
        $this->label = $label;
    }

    public function getIcon(): ?string {
        return $this->icon;
    }

    public function setIcon(?string $icon): void {
        $this->icon = $icon;
    }



    // Copy
    public function copy(): static {
        return new static(
            $this->source,
            $this->pattern
        );
    }
}
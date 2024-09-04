<?php

namespace core\path;

use core\Pipeline;

readonly class UrlPath implements Pipeline {
    public const START_POINTER = -1;



    public static function from(string $literal): self {
        $segments = explode('/', $literal);
        return new self($segments, self::nextPointer($segments, self::START_POINTER));
    }

    protected static function nextPointer(array $segments, int $pointer): int {
        $count = count($segments);

        for ($i = $pointer + 1; $i < $count; $i++) {
            if ($segments[$i] !== "") {
                break;
            }
        }

        return $i;
    }



    /**
     * @param array<string> $segments
     * @param int $pointer
     */
    public function __construct(
        protected array $segments,
        protected int $pointer
    ) {}



    public function getRemaining(): array {
        return array_slice($this->segments, $this->pointer);
    }

    public function next(): self {
        return new self(
            $this->segments,
            self::nextPointer($this->segments, $this->pointer)
        );
    }

    public function isExhausted(): bool {
        return $this->pointer >= count($this->segments);
    }

    public function current(): string {
        return $this->segments[$this->pointer];
    }

    public function __toString(): string {
        $ret = [];

        for ($i = 0; $i < count($this->segments); $i++) {
            $ret[] = $i === $this->pointer
                ? ' '. strtoupper($this->segments[$i]) .' '
                : $this->segments[$i];
        }

        return implode('/', $ret) .'; '. $this->pointer .'; '. json_encode($this->isExhausted());
    }
}
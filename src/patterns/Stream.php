<?php

namespace patterns;

use core\Pipeline;

class Stream implements Seek, Pipeline {
    protected int $pointer;
    protected int $resourceLength;



    public function __construct(
        protected string $resource
    ) {
        $this->pointer = 0;
        $this->resourceLength = strlen($this->resource);
    }



    function next(): string {
        return $this->resource[$this->pointer++];
    }

    function isExhausted(): bool {
        return $this->pointer >= $this->resourceLength;
    }

    function seek(int $offset): void {
        $this->pointer = $offset;
    }

    function skip(int $steps): void {
        $this->pointer += $steps;
    }

    public function getPointer(): int {
        return $this->pointer;
    }

    function current(): string {
        return $this->resource[$this->pointer];
    }

    public function __toString(): string {
        return substr($this->resource, 0, $this->pointer)
            . "[> "
            . ($this->resource[$this->pointer] ?? "\\0")
            . " <]"
            . substr($this->resource, $this->pointer + 1);
    }
}
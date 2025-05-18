<?php

namespace core\collection\iterator;

trait ArrayIteratorTrait {
    protected int $arrayIterator;

    public function current(): string {
        return $this->arrayIterator()[$this->key()];
    }

    public function next(): void {
        $this->arrayIterator++;
    }

    public function valid(): bool {
        return $this->arrayIterator < count($this->arrayIterator());
    }

    public function rewind(): void {
        $this->arrayIterator = 0;
    }
}
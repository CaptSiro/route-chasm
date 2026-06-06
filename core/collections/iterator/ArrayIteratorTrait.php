<?php

namespace core\collections\iterator;

trait ArrayIteratorTrait {
    protected int $arrayIteratorIndex;

    public function current(): string {
        return $this->getArrayIterator()[$this->key()] ?? '';
    }

    public function next(): void {
        $this->arrayIteratorIndex++;
    }

    public function valid(): bool {
        return $this->arrayIteratorIndex < count($this->getArrayIterator());
    }

    public function rewind(): void {
        $this->arrayIteratorIndex = 0;
    }
}
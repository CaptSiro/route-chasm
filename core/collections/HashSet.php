<?php

namespace core\collections;

use core\collections\iterator\ArrayIterator;
use core\collections\iterator\ArrayIteratorTrait;

/**
 * @template T
 * @template-implements Set<T>
 * @template-implements ArrayIterator<string, T>
 */
class HashSet implements Set, ArrayIterator {
    use ArrayIteratorTrait;

    /**
     * @var array<string, T>
     */
    private array $items = [];

    public function has(mixed $value): bool {
        return isset($this->items[$this->hash($value)]);
    }

    public function add(mixed $value): void {
        $this->items[$this->hash($value)] = $value;
    }

    public function remove(mixed $value): void {
        unset($this->items[$this->hash($value)]);
    }

    /**
     * @param T $value
     * @return string
     */
    private function hash(mixed $value): string {
        return is_object($value) ? spl_object_hash($value) : serialize($value);
    }



    // ArrayIterator
    public function getArrayIterator(): array {
        return $this->items;
    }

    public function key(): string {
        return array_keys($this->items)[$this->arrayIteratorIndex];
    }
}
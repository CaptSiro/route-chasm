<?php

namespace core\collections\iterator;

use Iterator;

/**
 * @template TKey
 * @template-covariant TValue
 * @template-implements Iterator<TKey, TValue>
 */
interface ArrayIterator extends Iterator {
    public function getArrayIterator(): array;
}
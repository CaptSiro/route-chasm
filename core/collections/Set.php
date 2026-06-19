<?php

namespace core\collections;

/**
 * @template T
 */
interface Set {
    /**
     * Checks if the set contains the given value.
     *
     * @param T $value
     * @return bool
     */
    public function has(mixed $value): bool;

    /**
     * Adds a value to the set.
     *
     * @param T $value
     * @return void
     */
    public function add(mixed $value): void;

    /**
     * Removes a value from the set.
     *
     * @param T $value
     * @return void
     */
    public function remove(mixed $value): void;
}
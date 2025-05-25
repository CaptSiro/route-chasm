<?php

namespace core\collections;

use core\Copy;

/**
 * @template T
 */
interface Dictionary extends Copy {
    public function exists(string $name): bool;

    /**
     * @param string $name
     * @param T $value
     * @return void
     */
    public function set(string $name, mixed $value): void;

    /**
     * @param string $name
     * @param T|null $or
     * @return T|null
     */
    public function get(string $name, mixed $or = null): mixed;

    /**
     * @param string $name
     * @return T|null
     */
    public function remove(string $name): mixed;

    public function clear(): void;

    /**
     * @param array<T> $array
     * @return void
     */
    public function load(array $array): void;

    /**
     * @return array<string, T>
     */
    public function toArray(): array;
}
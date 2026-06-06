<?php

namespace core\cache;

/**
 * @template T
 */
interface Cache {
    public function has(string $variable): bool;

    /**
     * @param string $variable
     * @return T
     */
    public function get(string $variable): mixed;

    /**
     * @param string $variable
     * @param T $value
     * @return self
     */
    public function set(string $variable, mixed $value): static;

    public function delete(string $variable): static;

    public function save(): static;

    public function toString(): string;
}
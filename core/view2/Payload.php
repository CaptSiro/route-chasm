<?php

namespace core\view2;

/**
 * @template T
 */
interface Payload {
    public function getViewReference(): View;

    /**
     * @param string $property
     * @return ?T
     */
    public function get(string $property): mixed;

    public function has(string $property): bool;

    public function all(): array;
}
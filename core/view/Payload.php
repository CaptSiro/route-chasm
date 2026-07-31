<?php

namespace core\view;

/**
 * @template T
 */
interface Payload {
    public function getViewReference(): View;

    /**
     * @param string $property
     * @return ?T
     */
    public function getProperty(string $property): mixed;

    public function getPropertyBoolean(string $property, bool $default = false): bool;

    public function getPropertyArray(string $property): array;

    /**
     * @param string $property
     * @param T $value
     * @return static
     */
    public function setProperty(string $property, mixed $value): static;

    public function hasProperty(string $property): bool;

    public function allProperties(): array;

    public function addProperty(string $arrayProperty, mixed $value, ?string $key): static;

    public function addAllProperties(string $arrayProperty, array $values): static;
}
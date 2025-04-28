<?php

namespace core\collection;

use core\Copy;

interface Dictionary extends Copy {
    public function exists(string $name): bool;

    public function set(string $name, mixed $value): void;

    public function get(string $name, mixed $or = null): mixed;

    public function remove(string $name): mixed;

    public function load(array $array): void;

    public function toArray(): array;
}
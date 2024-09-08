<?php

namespace core\dictionary;

interface Dictionary {
    public function exists(string $name): bool;

    public function set(string $name, mixed $value): void;

    public function get(string $name, mixed $or = null): mixed;

    public function load(array $array): void;
}
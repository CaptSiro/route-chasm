<?php

namespace core\dictionary;

interface Dictionary {
    function exists(string $name): bool;
    function set(string $name, mixed $value): void;
    function get(string $name, mixed $or = null): mixed;
    function load(array $array): void;
}
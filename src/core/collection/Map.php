<?php

namespace core\collection;

use core\Flags;
use JsonSerializable;

class Map implements Dictionary, JsonSerializable {
    use Flags;

    private array $map;



    public function __construct(array $array = []) {
        $this->map = $array;
    }

    function get(string $name, $or = null): mixed {
        return $this->map[$name] ?? $or;
    }

    function set(string $name, mixed $value): void {
        $this->map[$name] = $value;
    }

    function exists(string $name): bool {
        return isset($this->map[$name]);
    }

    function load(array $array): void {
        $this->map = array_merge($this->map, $array);
    }

    function clear(): void {
        $this->map = [];
    }

    public function jsonSerialize(): array {
        return $this->map;
    }
}
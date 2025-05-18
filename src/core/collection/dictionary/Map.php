<?php

namespace core\collection\dictionary;

use core\collection\Dictionary;
use core\Flags;
use core\utils\Arrays;
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

    public function toArray(): array {
        return $this->map;
    }

    public function jsonSerialize(): array {
        return $this->map;
    }

    public function remove(string $name): mixed {
        $value = $this->get($name);
        unset($this->map[$name]);
        return $value;
    }

    public function copy(): static {
        $instance = new static();
        $instance->map = Arrays::copy($this->map);
        return $instance;
    }
}
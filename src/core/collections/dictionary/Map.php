<?php

namespace core\collections\dictionary;

use core\collections\Dictionary;
use core\Flags;
use core\utils\Arrays;
use JsonSerializable;

/**
 * @template T
 * @template-implements Dictionary<T>
 */
class Map implements Dictionary, JsonSerializable {
    use Flags;

    /**
     * @var array<string, T>
     */
    private array $map;



    public function __construct(array $array = []) {
        $this->map = $array;
    }

    public function get(string $name, $or = null): mixed {
        return $this->map[$name] ?? $or;
    }

    public function set(string $name, mixed $value): void {
        $this->map[$name] = $value;
    }

    public function exists(string $name): bool {
        return isset($this->map[$name]);
    }

    public function load(array $array): void {
        $this->map = array_merge($this->map, $array);
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

    public function clear(): void {
        $this->map = [];
    }

    public function copy(): static {
        $instance = new static();
        $instance->map = Arrays::copy($this->map);
        return $instance;
    }
}
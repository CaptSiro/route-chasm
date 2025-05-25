<?php

namespace core\collections\dictionary;

use core\collections\StrictDictionary;
use core\utils\Arrays;

/**
 * @template T
 * @template-implements StrictDictionary<T>
 */
class StrictStack implements StrictDictionary {
    /**
     * @var array<array<string, T>>
     */
    private array $stack;



    public function __construct() {
        $this->stack = [];
    }



    public function get($name, $or = null): mixed {
        foreach (Arrays::reversed($this->stack) as $segment) {
            if (isset($segment[$name])) {
                return $segment[$name];
            }
        }

        return $or;
    }

    public function set(string $name, mixed $value): void {
        $head = array_key_first($this->stack);
        if ($head === null) {
            $this->stack[] = [];
            $head = array_key_first($this->stack);
        }

        $this->stack[$head][$name] = $value;
    }

    public function getStrict($name): mixed {
        $value = $this->get($name);
        if ($value === null) {
            throw new NotDefinedException($name);
        }

        return $value;
    }

    public function load(array $array): void {
        $this->push($array);
    }

    public function push(array $segment): void {
        $this->stack[] = $segment;
    }

    public function pop(): array {
        return array_pop($this->stack);
    }

    function exists(string $name): bool {
        foreach ($this->stack as $segment) {
            if (isset($segment[$name])) {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array {
        $count = count($this->stack);
        if ($count <= 0) {
            return [];
        }

        if ($count === 1) {
            return $this->stack[0];
        }

        return array_merge(...$this->stack);
    }

    public function remove(string $name): mixed {
        foreach (Arrays::reversed($this->stack) as $segment) {
            if (isset($segment[$name])) {
                $value = $segment[$name];
                unset($segment[$name]);
                return $value;
            }
        }

        return null;
    }

    public function clear(): void {
        $this->stack = [];
    }

    /**
     * @return $this<T>
     */
    public function copy(): static {
        $instance = new static();

        foreach ($this->stack as $item) {
            $instance->stack[] = Arrays::copy($item);
        }

        return $instance;
    }
}
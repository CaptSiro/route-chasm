<?php

namespace core\dictionary;

use Closure;
use core\utils\Arrays;

class StrictStack implements StrictDictionary {
    private array $stack;



    public function __construct(
        protected Closure $notDefinedFn
    ) {
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
            ($this->notDefinedFn)($name);
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
}
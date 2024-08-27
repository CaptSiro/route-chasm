<?php

namespace core\dictionary;

use Closure;

readonly class StrictMap implements StrictDictionary {
    private Map $map;



    public function __construct(
        private Closure $notDefinedFn,
        array $array,
    ) {
        $this->map = new Map($array);
    }



    function get(string $name, mixed $or = null): mixed {
        return $this->map->get($name, $or);
    }

    function getStrict(string $name): mixed {
        if (!$this->exists($name)) {
            ($this->notDefinedFn)($name);
        }

        return $this->map->get($name);
    }

    function exists(string $name): bool {
        return $this->map->exists($name);
    }

    function load(array $array): void {
        $this->map->load($array);
    }

    function set(string $name, mixed $value): void {
        $this->map->set($name, $value);
    }
}
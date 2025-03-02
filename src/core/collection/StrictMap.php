<?php

namespace core\collection;

use JsonSerializable;

readonly class StrictMap implements StrictDictionary, JsonSerializable {
    private Map $map;



    public function __construct(array $array = []) {
        $this->map = new Map($array);
    }



    function get(string $name, mixed $or = null): mixed {
        return $this->map->get($name, $or);
    }

    function getStrict(string $name): mixed {
        if (!$this->exists($name)) {
            throw new NotDefinedException($name);
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

    public function getMap(): Map {
        return $this->map;
    }

    public function jsonSerialize(): Map {
        return $this->map;
    }

    public function asArray(): array {
        return $this->map->asArray();
    }
}
<?php

namespace core\tree\traversable;

class MatchStack {
    private array $stack;



    public function __construct() {
        $this->stack = [];
    }



    public function isEmpty(): bool {
        return empty($this->stack);
    }

    public function push(array $params, array $endpoints): void {
        $item = [];

        if (!empty($params)) {
            $item["params"] = $params;
        }

        if (!empty($endpoints)) {
            $item["endpoints"] = $endpoints;
        }

        $this->stack[] = $item;
        var_dump(__METHOD__);
        var_dump($item);
    }

    public function pop(): void {
        array_pop($this->stack);
        var_dump(__METHOD__);
        var_dump($this);
    }

    public function merge(?array &$params, ?array &$endpoints): void {
        var_dump(__METHOD__);
        var_dump($this);
        $params = $endpoints = [];

        foreach ($this->stack as $item) {
            if (isset($item["params"])) {
                $params = array_merge($params, $item["params"]);
            }

            if (isset($item["endpoints"])) {
                $endpoints = array_merge($endpoints, $item["endpoints"]);
            }
        }
    }
}
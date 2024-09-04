<?php

namespace core\tree;

class SnapshotStack {
    protected const ENDPOINTS = 0;
    protected const PARAMETERS = 1;



    private array $stack;



    public function __construct() {
        $this->stack = [];
    }



    public function isEmpty(): bool {
        return empty($this->stack);
    }

    public function push(array $params, array $endpoints): void {
        $item = [];

        $item[self::PARAMETERS] = $params;
        $item[self::ENDPOINTS] = $endpoints;

        $this->stack[] = $item;
    }

    public function pop(): void {
        array_pop($this->stack);
    }

    public function merge(): Trail {
        $params = $endpoints = [];

        foreach ($this->stack as $item) {
            if (!empty($item[self::PARAMETERS])) {
                $params = array_merge($params, $item[self::PARAMETERS]);
            }

            if (!empty($item[self::ENDPOINTS])) {
                $endpoints = array_merge($endpoints, $item[self::ENDPOINTS]);
            }
        }

        return new Trail($params, $endpoints);
    }
}
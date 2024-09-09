<?php

namespace core\tree;

use core\endpoints\Endpoint;
use core\Flags;

class Trail {
    use Flags;



    /**
     * @param array<string, string> $params
     * @param array<Endpoint> $endpoints
     */
    public function __construct(
        protected array $params,
        protected readonly array $endpoints,
    ) {}



    /**
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    public function getParam(string $name): ?string {
        return $this->params[$name] ?? null;
    }

    public function setParam(string $name, string $value): void {
        $this->params[$name] = $value;
    }

    /**
     * @return array
     */
    public function getEndpoints(): array {
        return $this->endpoints;
    }

    public function __toString(): string {
        return 'Endpoints: '
            . json_encode(array_map(fn($x) => get_class($x), $this->endpoints))
            . '; Parameters: '
            . json_encode($this->params);
    }
}
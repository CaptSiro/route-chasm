<?php

namespace core\tree\traversable;

use core\endpoints\Endpoint;

readonly class FoundNode {
    /**
     * @param array<string, string> $matches
     * @param array<Endpoint> $endpoints
     */
    public function __construct(
        public array $matches,
        public array $endpoints,
        public int $flags
    ) {}
}
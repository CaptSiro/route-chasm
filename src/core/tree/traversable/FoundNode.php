<?php

namespace core\tree\traversable;

use core\endpoints\Endpoint;
use core\Flags;

class FoundNode {
    use Flags;

    /**
     * @param array<string, string> $matches
     * @param array<Endpoint> $endpoints
     */
    public function __construct(
        public readonly array $matches,
        public readonly array $endpoints,
    ) {}
}
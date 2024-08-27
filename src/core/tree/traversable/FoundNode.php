<?php

namespace core\tree\traversable;

readonly class FoundNode {
    public function __construct(
        public array $matches,
        public array $endpoints
    ) {}
}
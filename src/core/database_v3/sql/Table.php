<?php

namespace core\database_v3\sql;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Table {
    public function __construct(
        public string $name
    ) {}
}
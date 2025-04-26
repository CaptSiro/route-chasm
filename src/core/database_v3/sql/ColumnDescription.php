<?php

namespace core\database_v3\sql;

readonly class ColumnDescription {
    public function __construct(
        public string $alias,
        public string $name,
        public string $type
    ) {}
}
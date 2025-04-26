<?php

namespace core\database_v3\sql;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Database {
    public function __construct(
        public ?string $connectionName = null
    ) {}



    public function getConnection(): Connection {
        return Sql::getConnection($this->connectionName);
    }
}
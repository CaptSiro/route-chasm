<?php

namespace core\database\sql;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Database {
    public function __construct(
        protected ?string $connectionName = null
    ) {}



    public function getConnectionName(): ?string {
        return $this->connectionName;
    }

    public function getConnection(): Connection {
        return Sql::getConnection($this->connectionName);
    }
}
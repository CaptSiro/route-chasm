<?php

namespace core\database\sql\config;

readonly class BasicSqlConfig implements SqlConfig {
    public function __construct(
        protected string $host,
        protected string $databaseName,
        protected string $user,
        protected string $password,
        protected string $port = "3306",
        protected string $charset = "UTF8"
    ) {}

    public function getConnectionString(): string {
        return "mysql:host=" . $this->host
            . ";port=" . $this->port
            . ";dbname=" . $this->databaseName
            . ";charset=" . $this->charset;
    }

    public function getDatabaseUser(): string {
        return $this->user;
    }

    public function getDatabasePassword(): string {
        return $this->password;
    }

    public function jsonSerialize(): string {
        return $this->user .' '. $this->getConnectionString();
    }
}
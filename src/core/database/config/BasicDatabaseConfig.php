<?php

namespace core\database\config;

readonly class BasicDatabaseConfig implements DatabaseConfig {
    public function __construct(
        protected string $host,
        protected string $databaseName,
        protected string $user,
        protected string $password,
        protected string $port = "3306",
        protected string $charset = "UTF8"
    ) {}

    function getDatabaseHost(): string {
        return $this->host;
    }

    function getDatabasePort(): string {
        return $this->port;
    }

    function getDatabaseName(): string {
        return $this->databaseName;
    }

    function getDatabaseUser(): string {
        return $this->user;
    }

    function getDatabasePassword(): string {
        return $this->password;
    }

    function getDatabaseCharset(): string {
        return $this->charset;
    }
}
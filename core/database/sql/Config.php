<?php

namespace core\database\sql;

readonly class Config {
    public function __construct(
        public string $host,
        public string $databaseName,
        public string $user,
        public string $password,
        public string $port = "3306",
        public string $charset = "UTF8"
    ) {}

    public function getConnectionString(string $protocol): string {
        return "$protocol:host=" . $this->host
            . ";port=" . $this->port
            . ";dbname=" . $this->databaseName
            . ";charset=" . $this->charset;
    }
}
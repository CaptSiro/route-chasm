<?php

namespace core\database_v3\sql\connections;

use core\database_v3\sql\Config;
use core\database_v3\sql\Connection;
use core\database_v3\sql\Driver;
use PDO;

class MySqlDriver implements Driver {
    public const PROTOCOL = 'mysql';

    public function __construct(
        protected Config $config,
        protected ?array $options = null
    ) {}



    public function setOptions(array $options): void {
        $this->options = $options;
    }

    public function connect(): Connection {
        $connection = new PDO(
            $this->config->getConnectionString(self::PROTOCOL),
            $this->config->user,
            $this->config->password,
            $this->options ?? [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
            ]
        );

        return new PdoConnection($connection, $this);
    }

    public function escapeTable(string $table): string {
        return "`$table`";
    }

    public function escapeColumn(string $column): string {
        return "`$column`";
    }
}
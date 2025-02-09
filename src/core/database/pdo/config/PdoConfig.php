<?php

namespace core\database\pdo\config;

use JsonSerializable;

interface PdoConfig extends JsonSerializable {
    public function getConnectionString(): string;

    /**
     * Getter for USER argument for PDO constructor
     *
     * @return string
     */
    public function getDatabaseUser(): string;

    /**
     * Getter for PASSWORD argument for PDO constructor
     *
     * @return string
     */
    public function getDatabasePassword(): string;
}
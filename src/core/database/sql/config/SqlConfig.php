<?php

namespace core\database\sql\config;

use JsonSerializable;

interface SqlConfig extends JsonSerializable {
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
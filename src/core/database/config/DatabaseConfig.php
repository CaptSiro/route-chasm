<?php

namespace core\database\config;

interface DatabaseConfig {
    /**
     * Getter for HOST parameter for database connection string
     *
     * @return string
     */
    public function getDatabaseHost(): string;

    /**
     * Getter for PORT parameter for database connection string
     *
     * @return string
     */
    public function getDatabasePort(): string;

    /**
     * Getter for DBNAME parameter for database connection string
     *
     * @return string
     */
    public function getDatabaseName(): string;

    /**
     * Getter for CHARSET parameter for database connection string
     *
     * @return string
     */
    public function getDatabaseCharset(): string;

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
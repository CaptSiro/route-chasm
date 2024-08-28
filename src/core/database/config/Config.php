<?php

namespace core\database\config;

interface Config {
    /**
     * Getter for HOST parameter for database connection string
     *
     * @return string
     */
    function getDatabaseHost(): string;

    /**
     * Getter for PORT parameter for database connection string
     *
     * @return string
     */
    function getDatabasePort(): string;

    /**
     * Getter for DBNAME parameter for database connection string
     *
     * @return string
     */
    function getDatabaseName(): string;

    /**
     * Getter for CHARSET parameter for database connection string
     *
     * @return string
     */
    function getDatabaseCharset(): string;

    /**
     * Getter for USER argument for PDO constructor
     *
     * @return string
     */
    function getDatabaseUser(): string;

    /**
     * Getter for PASSWORD argument for PDO constructor
     *
     * @return string
     */
    function getDatabasePassword(): string;
}
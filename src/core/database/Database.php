<?php

namespace core\database;

use core\database\query\Query;

interface Database {
    public function getLastInsertedId(): false|string;

    public function run(string|Query $query): SideEffect;

    public function fetch(string|Query $query, string $class): ?Table;

    /**
     * @param string|Query $query
     * @param string $class <code>Table</code> class
     * @return array<Table>|null
     */
    public function fetchAll(string|Query $query, string $class): ?array;
}
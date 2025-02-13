<?php

namespace core\database;

use core\database\query\Query;

interface Database {
    public function getLastInsertedId(): false|string;

    public function run(string|Query $query): SideEffect;

    public function fetch(string|Query $query, ?string $class = null): ?Entity;

    /**
     * @param string|Query $query
     * @param string|null $class <code>Entity</code> class
     * @return array<Entity>|null
     */
    public function fetchAll(string|Query $query, ?string $class = null): ?array;
}
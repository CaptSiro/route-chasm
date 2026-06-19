<?php

namespace core\database\sql;

use core\database\sql\query\Query;

interface Connection {
    public function fetch(Query $query): ?array;

    public function fetchAll(Query $query): array;

    public function run(Query $query): SideEffect;

    public function getDriver(): Driver;
}
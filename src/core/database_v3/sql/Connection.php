<?php

namespace core\database_v3\sql;

use core\database_v3\sql\query\Query;

interface Connection {
    public function fetch(Query $query): ?array;

    public function fetchAll(Query $query): array;

    public function run(Query $query): SideEffect;

    public function getDriver(): Driver;
}
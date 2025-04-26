<?php

namespace core\database_v3\sql\query;

use core\database_v3\sql\Connection;

interface SqlQuery {
    public function toQuery(Connection $connection): Query;
}
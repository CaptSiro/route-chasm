<?php

namespace core\database\sql\query;

use core\database\sql\Connection;

interface SqlQuery {
    public function toQuery(Connection $connection): Query;
}
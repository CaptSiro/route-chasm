<?php

namespace core\database_v3\sql\query;

interface SqlQuery {
    public function toQuery(): Query;
}
<?php

namespace core\database_v3\sql;

interface Driver {
    public function connect(): Connection;
}
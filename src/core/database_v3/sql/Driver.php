<?php

namespace core\database_v3\sql;

interface Driver extends Escape {
    public function connect(): Connection;
}
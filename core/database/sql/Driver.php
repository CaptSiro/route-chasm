<?php

namespace core\database\sql;

interface Driver extends Escape {
    public function connect(): Connection;
}
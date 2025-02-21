<?php

namespace core\config;

use core\database\sql\config\SqlConfig;

interface Config {
    public function getSqlConfig(): SqlConfig;
}
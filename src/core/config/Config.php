<?php

namespace core\config;

use core\database\pdo\config\PdoConfig;

interface Config {
    public function getPdoConfig(): PdoConfig;
}
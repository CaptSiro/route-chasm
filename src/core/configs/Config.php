<?php

namespace core\configs;

use core\database\sql\Config as SqlConfig;

interface Config {
    public function getSqlConfig(): SqlConfig;

    public function getPublicDirectory(): string;
}
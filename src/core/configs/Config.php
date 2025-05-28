<?php

namespace core\configs;

use core\database\sql\Config as SqlConfig;
use core\route\compiler\RouteCompiler;

interface Config {
    public function getSqlConfig(): SqlConfig;

    public function getPublicDirectory(): string;

    public function getRouteCompiler(): RouteCompiler;
}
<?php

namespace core\utils;

class Php {
    public static function run(string $script, bool $once = true): void {
        if ($once) {
            require_once $script;
            return;
        }

        require $script;
    }
}
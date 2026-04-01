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

    public static function get(Ini $option): ?string {
        return ($value = ini_get($option->value)) !== false
            ? $value
            : null;
    }
}
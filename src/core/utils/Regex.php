<?php

namespace core\utils;

class Regex {
    public static function create(string $pattern): string {
        return "/^$pattern$/";
    }

    public static function createNamedGroup(string $name, string $regex): string {
        return "(?<$name>$regex)";
    }
}
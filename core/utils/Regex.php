<?php

namespace core\utils;

class Regex {
    public const LOCALE_WILDCARD = '/.*/';
    public const LOCALE_RULE_ONE = '/^1$/';
    public const LOCALE_RULE_TWO_TO_FOUR = '/^[2-4]$/';
    public const LOCALE_RULE_TWO_AND_UP = '/^0|[2-9]|\d{2,}$/';
    public const LOCALE_RULE_FIVE_AND_UP = '/^0|[5-9]|\d{2,}$/';



    public static function create(string $pattern): string {
        return "/^$pattern$/";
    }

    public static function createNamedGroup(string $name, string $regex): string {
        return "(?<$name>$regex)";
    }
}
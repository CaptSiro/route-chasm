<?php

namespace core;

trait EnumOptions {
    public static function options(): array {
        $ret = [];

        foreach (self::cases() as $case) {
            $ret[$case->name] = $case->value;
        }

        return $ret;
    }

    public static function fromOption(string $name): ?static {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }
}
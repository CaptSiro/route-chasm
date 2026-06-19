<?php

namespace core\utils;

class Objects {
    public static function normalizeClass(string $class): string {
        return str_replace('\\', '/', $class);
    }

    public static function getClass(mixed $object): string {
        return basename(self::normalizeClass(get_class($object)));
    }

    public static function getClassStatic(string $class): string {
        return basename(self::normalizeClass($class));
    }
}

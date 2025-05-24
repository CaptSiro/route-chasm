<?php

namespace core\utils;

class Objects {
    public static function getClass(mixed $object): string {
        return basename(get_class($object));
    }

    public static function getClassStatic(string $class): string {
        return basename($class);
    }
}
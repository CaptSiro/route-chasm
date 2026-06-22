<?php

namespace core\storage;

class Temporary {
    private static string $directory;

    public static function directory(): string {
        if (isset(self::$directory)) {
            return self::$directory;
        }

        return self::$directory = Data::namespace('temp', create: true);
    }

    public static function file(string $prefix = 'rc_'): string {
        $tempDirectory = self::directory();
        return tempnam($tempDirectory, $prefix);
    }
}
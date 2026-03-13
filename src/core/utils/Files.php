<?php

namespace core\utils;

class Files {
    protected static function periodPosition($base): int {
        $len = strlen($base);

        for ($i = $len - 1; $i >= 0; $i--) {
            if ($base[$i] === ".") {
                return $i;
            }
        }

        return -1;
    }

    public static function extension(string $path): ?string {
        $base = basename($path);
        $len = strlen($base);

        for ($i = $len - 1; $i >= 0; $i--) {
            if ($base[$i] === ".") {
                return substr($base, $i + 1);
            }
        }

        return null;
    }

    /**
     * @param string $path
     * @return array<string> [name, extension]
     */
    public static function split(string $path): array {
        $base = basename($path);
        $period = self::periodPosition($base);

        if ($period < 0) {
            return [$base, ''];
        }

        $name = substr($base, 0, $period);
        $extension = substr($base, $period + 1);

        return [$name, $extension];
    }

    public static function removeExtension(string $path): string {
        $period = self::periodPosition($path);
        if ($period < 0) {
            return $path;
        }

        return substr($path, 0, $period);
    }

    public static function mimeType(string $path): string {
        $extension = self::extension($path);

        if ($extension === "css") {
            return "text/css";
        }

        if ($extension === "js") {
            return "text/js";
        }

        return mime_content_type($path);
    }

    public static function hashPath(string $file, string &$real = null): int|false {
        $real = realpath($file);

        if ($real === false) {
            return false;
        }

        return Strings::hashAscii($real);
    }

    /**
     * @param string $directory
     * @return array<string>
     */
    public static function fromDirectory(string $directory): array {
        $files = [];

        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $files[] = "$directory/$entry";
        }

        return $files;
    }

    public static function humanSize(int $bytes): string {
        if ($bytes < 1000) {
            return $bytes . ' B';
        }

        $units = ['kiB', 'MiB', 'GiB', 'TiB', 'PiB'];
        $value = $bytes;
        $i = 0;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        $formatted = number_format($value, ($value < 10 ? 1 : 0), '.', '');
        if (str_contains($formatted, '.')) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted . ' ' . $units[$i - 1];
    }
}
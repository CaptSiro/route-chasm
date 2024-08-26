<?php

namespace core\utils;

class Files {
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
}
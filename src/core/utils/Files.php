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
}
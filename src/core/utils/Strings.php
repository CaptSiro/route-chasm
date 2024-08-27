<?php

namespace core\utils;

use core\Init;
use patterns\Charset;

class Strings extends Init {
    protected static string $charsAlpha;
    protected static string $charsAlphaUpper;
    protected static string $charsNumbers;

    public static function init(): void {
        self::$charsAlpha = (new Charset())
            ->addRange('a', 'z')
            ->asString();

        self::$charsAlphaUpper = strtoupper(self::$charsAlpha);

        self::$charsNumbers = (new Charset())
            ->addRange('0', '9')
            ->asString();
    }

    public static function CHARS_ALPHA(): string {
        return self::$charsAlpha;
    }

    public static function CHARS_ALPHA_UPPER(): string {
        return self::$charsAlphaUpper;
    }

    public static function CHARS_NUMBERS(): string {
        return self::$charsNumbers;
    }

    public static function CHARS_SPECIALS(): string {
        return " !@#$%^&*()-_=+[{]}\\|;:'\",<.>/?";
    }

    public static function positions(string $needle, string $haystack, int $offset): array {
        $occurrences = [];

        while (($pos = strpos($haystack, $needle, $offset)) !== false) {
            $occurrences[] = $pos;
            $offset = $pos + 1;
        }

        return $occurrences;
    }
}
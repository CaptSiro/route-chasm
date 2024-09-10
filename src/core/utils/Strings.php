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

    /**
     * Java implementation of <code>String.hashCode</code> method
     * @see https://stackoverflow.com/questions/15518418/whats-behind-the-hashcode-method-for-string-in-java
     * @param string $string
     * @return int
     */
    public static function hashAscii(string $string): int {
        $len = strlen($string);

        if ($len === 0) {
            return 0;
        }

        $hash = 0;

        for ($i = 0; $i < $len; $i++) {
            $hash = (int) (31 * $hash + ord($string[$i]));
        }

        return $hash;
    }

    public static function encodeBase64Safe(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function decodeBase64Safe(string $data): string {
        return base64_decode(str_pad(
            strtr($data, '-_', '+/'),
            strlen($data) % 4,
            '='
        ));
    }
}
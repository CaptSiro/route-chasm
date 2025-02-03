<?php

namespace core\utils;

use core\Init;
use core\patterns\Charset;

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

        return $hash >> 16;
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

    public static function split(string $haystack, string $needle, ?string &$rest): ?string {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            $rest = $haystack;
            return null;
        }

        $rest = substr($haystack, $pos + strlen($needle));
        return substr($haystack, 0, $pos);
    }

    public static function prepend(string $start, string $subject, bool $skipIfPresent = true): string {
        if ($skipIfPresent && str_starts_with($subject, $start)) {
            return $subject;
        }

        return $start . $subject;
    }

    public static function parseUrlEncoded(string $string): array {
        $array = [];
        $length = strlen($string);
        $name = "";
        $value = "";
        $isValueTarget = false;

        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] === '=') {
                $isValueTarget = true;
                continue;
            }

            if ($string[$i] == "&") {
                $array[$name] = urldecode($value);
                $name = "";
                $value = "";
                $isValueTarget = false;
                continue;
            }

            if ($isValueTarget) {
                $value .= $string[$i];
                continue;
            }

            $name .= $string[$i];
        }

        return $array;
    }

    public static function toBytes(string $formattedBytes): ?int {
        $units = ['B', 'K', 'M', 'G', 'T', 'P'];
        $unitsExtended = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $number = (int) preg_replace("/[^0-9]+/", "", $formattedBytes);
        $suffix = preg_replace("/[^a-zA-Z]+/", "", $formattedBytes);

        if(is_numeric($suffix[0])) {
            return preg_replace('/\D/', '', $formattedBytes);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            $exponent = array_flip($unitsExtended)[$suffix] ?? null;
        }

        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }
}
<?php

namespace core\utils;

use core\Init;
use core\patterns\Charset;
use Transliterator;

class Strings extends Init {
    protected static string $charsAlpha;
    protected static Charset $charsetAlpha;
    protected static string $charsAlphaUpper;
    protected static Charset $charsetAlphaUpper;
    protected static string $charsAllAlpha;
    protected static string $charsNumbers;

    public static function init(): void {
        self::$charsetAlpha = (new Charset())->addRange('a', 'z');
        self::$charsAlpha = self::$charsetAlpha->asString();

        self::$charsetAlphaUpper = (new Charset())->addRange('A', 'Z');
        self::$charsAlphaUpper = strtoupper(self::$charsAlpha);
        self::$charsAllAlpha = self::$charsAlpha . self::$charsAlphaUpper;

        self::$charsNumbers = (new Charset())
            ->addRange('0', '9')
            ->asString();
    }

    public static function nonEmpty(?string $value, string $or): string {
        return !empty($value)
            ? $value
            : $or;
    }

    public static function CHARS_ALPHA(): string {
        return self::$charsAlpha;
    }

    public static function CHARS_ALPHA_UPPER(): string {
        return self::$charsAlphaUpper;
    }

    public static function CHARS_ALL_ALPHA(): string {
        return self::$charsAllAlpha;
    }

    public static function CHARS_NUMBERS(): string {
        return self::$charsNumbers;
    }

    public static function CHARS_SPECIALS(): string {
        return " !@#$%^&*()-_=+[{]}\\|;:'\",<.>/?";
    }

    public static function isUpper(string $char): string {
        return self::$charsetAlphaUpper->contains($char);
    }

    public static function isLower(string $char): string {
        return self::$charsetAlpha->contains($char);
    }

    public static function randomChar(string $charset): string {
        $len = strlen($charset);
        return $charset[rand(0, $len - 1)];
    }

    public static function randomBase64(int $length, string $specialA = '-', string $specialB = '_'): string {
        $charset = self::$charsAlpha . self::$charsAlphaUpper . self::$charsNumbers . $specialA . $specialB;
        $len = strlen($charset);

        $buffer = '';
        for ($i = 0; $i < $length; $i++) {
            $buffer .= $charset[rand(0, $len - 1)];
        }

        return $buffer;
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

    public static function lpad(string $padding, string $subject, int $length): string {
        return str_pad($subject, $length, $padding, STR_PAD_LEFT);
    }

    public static function parseUrlEncoded(string $string): array {
        $ret = [];

        foreach (explode('&', $string) as $pair) {
            $x = explode('=', $pair, 2);
            $ret[$x[0]] = $x[1] ?? "";
        }

        return $ret;
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

    public static function identifier(string $unsafe): ?string {
        if ($unsafe === "") {
            return "";
        }

        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC');

        $ascii = $transliterator
            ? $transliterator->transliterate($unsafe)
            : $unsafe;

        $slug = preg_replace('~[^A-Za-z0-9]+~', '-', $ascii);
        $slug = trim($slug, '-');
        $slug = strtolower($slug);

        return $slug !== ''
            ? $slug
            : null;
    }

    public static function fromHumanReadableBoolean(string $string): bool {
        return filter_var($string, FILTER_VALIDATE_BOOLEAN);
    }

    public static function toHumanReadable(mixed $value): string {
        return match (gettype($value)) {
            "string", "integer", "double" => (string) $value,
            "boolean" => $value ? 'Yes' : 'No',
            "array" => implode(', ', $value),
            "object" => json_encode($value),
            default => '',
        };
    }

    public static function fromBuffer(callable $bufferWriter): string {
        if (!ob_start()) {
            return "";
        }

        $bufferWriter();

        if (($content = ob_get_clean()) === false) {
            return "";
        }

        return $content;
    }

    public static function pascalToKebab(string $string): string {
        $len = strlen($string);
        if ($len === 0) {
            return '';
        }

        $ret = '';
        $wasPreviousUpper = false;

        for ($i = 0; $i < $len; $i++) {
            $char = $string[$i];
            $isUpper = self::isUpper($char);

            if (!$isUpper) {
                $wasPreviousUpper = false;
                $ret .= $char;
                continue;
            }

            if ($i + 1 >= $len) {
                if (!$wasPreviousUpper) {
                    $ret .= '-';
                }

                $wasPreviousUpper = true;
                $ret .= strtolower($char);
                continue;
            }

            $isNextLower = self::isLower($string[$i + 1]);
            if (($i !== 0) && (!$wasPreviousUpper || $isNextLower)) {
                $ret .= '-';
            }

            $wasPreviousUpper = true;
            $ret .= strtolower($char);
        }

        return $ret;
    }
}
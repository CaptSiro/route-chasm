<?php

namespace core\path\parser;

class Ident {
    static function validate(string $literal): bool {
        $len = strlen($literal);

        if ($len === 0) {
            return false;
        }

        if (!self::alpha($literal[0])) {
            return false;
        }

        for ($i = 1; $i < $len; $i++) {
            if (!self::alphanumeric($literal[$i])) {
                return false;
            }
        }

        return true;
    }



    static function alpha(string $char): bool {
        $code = mb_ord($char);

        return $char === "_"
            || (mb_ord("a") <= $code && $code <= mb_ord("z"))
            || (mb_ord("A") <= $code && $code <= mb_ord("Z"));
    }



    static function alphanumeric(string $char): bool {
        $code = mb_ord($char);

        return self::alpha($char)
            || (mb_ord("0") <= $code && $code <= mb_ord("9"));
    }
}
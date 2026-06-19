<?php

namespace core\utils;

class Numbers {
    public static function fraction(float $x): float {
        return $x - floor($x);
    }
}
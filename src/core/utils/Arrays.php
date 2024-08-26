<?php

namespace core\utils;

use Generator;

class Arrays {
    public static function reversed(array $array): Generator {
        $keys = array_keys($array);

        for ($i = count($keys) - 1; $i >= 0; $i--) {
            yield $array[$keys[$i]];
        }
    }

    public static function equal(array $a, array $b): bool {
        $c = count($a);

        if ($c !== count($b)) {
            return false;
        }

        $n = 0;

        foreach ($a as $key => $item) {
            $n++;

            if (!isset($b[$key]) || $b[$key] !== $item) {
                return false;
            }
        }

        return $n === $c;
    }
}
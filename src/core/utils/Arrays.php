<?php

namespace core\utils;

use Generator;

class Arrays {
    /**
     * @template T
     * @param array<T> $array
     * @return T
     */
    public static function first(array $array): mixed {
        return $array[array_key_first($array)];
    }

    /**
     * @template T
     * @param array<T> $array
     * @return T
     */
    public static function last(array $array): mixed {
        return $array[array_key_last($array)];
    }

    /**
     * @template S
     * @template T
     * @param array<S, T> $array
     * @return array<S, T>
     */
    public static function copy(array $array): array {
        return array_merge([], $array);
    }

    public static function push(array|null &$array, mixed $element): void {
        if (is_null($array)) {
            $array = [];
        }

        $array[] = $element;
    }

    public static function append(array &$array, $key, $value): void {
        if (!isset($array[$key])) {
            $array[$key] = $value;
            return;
        }

        if (is_array($array[$key])) {
            $array[$key][] = $value;
            return;
        }

        $array[$key] = [$array[$key], $value];
    }

    public static function explode(string $separator, string $subject, bool $filterEmpty = true): array {
        if (!$filterEmpty) {
            return explode($separator, $subject);
        }

        $buffer = [];
        foreach (explode($separator, $subject) as $item) {
            if ($item === '') {
                continue;
            }

            $buffer[] = $item;
        }

        return $buffer;
    }

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

    public static function urlEncode(array $array): string {
        $first = true;
        $buffer = "";

        foreach ($array as $name => $value) {
            if (is_null($value)) {
                $buffer .= ($first ? '' : '&') . urlencode($name);
                $first = false;
                continue;
            }

            $buffer .= ($first ? '' : '&') . urlencode($name) .'='. urlencode($value);
            $first = false;
        }

        return $buffer;
    }

    public static function htmlEncode(array $array): string {
        $first = true;
        $buffer = "";

        foreach ($array as $name => $value) {
            if (is_null($value) || $value === false) {
                continue;
            }

            if ($value === true) {
                $buffer .= ($first ? '' : ' ') . htmlspecialchars($name);
                $first = false;
                continue;
            }

            $buffer .= ($first ? '' : ' ') . htmlspecialchars($name) .'="'. htmlspecialchars($value) .'"';
            $first = false;
        }

        return $buffer;
    }
}
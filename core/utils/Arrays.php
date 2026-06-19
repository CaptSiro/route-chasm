<?php

namespace core\utils;

use Closure;
use core\Copy;
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
     * @template T
     * @param array<T> $array
     * @param Closure $keyGenerator
     * @return array<T>
     */
    public static function changeKeys(array $array, Closure $keyGenerator): array {
        $ret = [];

        foreach ($array as $key => $item) {
            $ret[($keyGenerator)($item, $key)] = $item;
        }

        return $ret;
    }

    /**
     * @param array $array
     * @param array $values
     * @return Closure Rewert changes to previous state
     */
    public static function set(array &$array, array $values): Closure {
        $copy = [...$array];

        $array = [];
        foreach ($values as $name => $value) {
            $array[$name] = $value;
        }

        return function () use ($copy, &$array) {
            $array = $copy;
        };
    }

    /**
     * @template S
     * @template T
     * @param array<S, T> $array
     * @return array<S, T>
     */
    public static function copy(array $array): array {
        return array_map(function ($x) {
            if ($x instanceof Copy) {
                return $x->copy();
            }

            return $x;
        }, array_merge([], $array));
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

    /**
     * @param array $array
     * @return Generator
     */
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
            if (empty($value)) {
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
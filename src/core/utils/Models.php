<?php

namespace core\utils;

use core\database\sql\Model;

class Models {
    public static function get(?Model $model, string $property, mixed $or = null): mixed {
        if (is_null($model)) {
            return $or;
        }

        return $model->$property;
    }

    public static function getString(?Model $model, string $property): string {
        return self::get($model, $property, '');
    }

    /**
     * @param array<Model> $models
     * @return array<int, Model>
     */
    public static function identity(array $models): array {
        $ret = [];

        foreach ($models as $model) {
            $ret[$model->getId()] = $model;
        }

        return $ret;
    }

    /**
     * Transpose an associative array of property arrays into an array of associative "objects".
     *
     * Input:
     * [
     *     "name" => ["john", "tonny"],
     *     "age"  => [11, 21],
     *     "ignored" => [true, false]
     * ]
     *
     * Output:
     * [
     *     ["name" => "john", "age" => 11],
     *     ["name" => "tonny", "age" => 21]
     * ]
     *
     * @param array<string, array> $data Associative array where each key is a property name and its value is an array of values.
     * @param array<string> $properties Properties to transpose
     * @param int $count Number of object to create
     * @return array<int, array<string, mixed>> Array of associative arrays (objects), each combining values by index.
     */
    public static function transpose(array $data, array $properties, int $count): array {
        $ret = [];

        for ($i = 0; $i < $count; $i++) {
            $object = [];

            foreach ($properties as $property) {
                if (!isset($data[$property][$i])) {
                    continue;
                }

                $object[$property] = $data[$property][$i];
            }

            $ret[] = $object;
        }

        return $ret;
    }
}
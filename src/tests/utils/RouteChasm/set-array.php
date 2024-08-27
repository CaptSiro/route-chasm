<?php

function set_array(array &$array, array $values): Closure {
    $copy = [...$array];

    foreach ($values as $name => $value) {
        $array[$name] = $value;
    }

    return function () use ($copy, &$array) {
        $array = $copy;
    };
}
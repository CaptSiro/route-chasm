<?php

namespace core\guards;

use components\core\SaveError\SaveError;

class NumberGuard {
    public static function inRange(
        int $x,
        int $min = PHP_INT_MIN,
        int $max = PHP_INT_MAX,
        ?string $property = null,
        ?string $message = null
    ): ?SaveError {
        if ($x >= $min && $x <= $max) {
            return null;
        }

        if (is_null($message)) {
            $message = is_null($property)
                ? "$x is not in range: $min upto $max"
                : ucfirst($property) ." is not in range: $min upto $max";
        }

        return new SaveError(
            $property,
            $message
        );
    }
}
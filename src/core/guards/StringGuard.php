<?php

namespace core\guards;

use components\core\Html\Html;
use components\core\SaveError\SaveError;
use core\patterns\Pattern;

class StringGuard {
    public static function nonEmpty(
        string $x,
        ?string $property = null,
        ?string $message = null,
    ): ?SaveError {
        if (!empty($x)) {
            return null;
        }

        if (is_null($message)) {
            $message = is_null($property)
                ? "Can not be left empty"
                : ucfirst($property) ." must not be empty";
        }

        return new SaveError(
            $property,
            $message
        );
    }

    public static function satisfiesPattern(
        string $x,
        Pattern $pattern,
        ?string $property = null,
        ?string $message = null
    ): ?SaveError {
        if ($pattern->match($x)) {
            return null;
        }

        if (is_null($message)) {
            $message = is_null($property)
                ? "'$x' does not satisfy pattern " // todo add Pattern::__toString()
                : ucfirst($property) ." does not satisfy pattern";
        }

        return new SaveError(
            $property,
            $message
        );
    }

    public static function satisfiesRegex(
        string $x,
        string $regex,
        ?string $property = null,
        ?string $message = null
    ): ?SaveError {
        if (preg_match($regex, $x)) {
            return null;
        }

        if (is_null($message)) {
            $message = is_null($property)
                ? "'$x' does not satisfy pattern"
                : ucfirst($property) ." does not satisfy pattern";
        }

        return new SaveError(
            $property,
            Html::escape($message)
        );
    }
}
<?php

namespace components\core;

class Icon {
    public static function nf(string $class, ?string $fallback = null): string {
        if (is_null($fallback)) {
            return "<i class='nf $class'></i>";
        }

        return "<i class='nf $class'><span>$fallback</span></i>";
    }
}
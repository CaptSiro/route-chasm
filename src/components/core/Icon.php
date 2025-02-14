<?php

namespace components\core;

class Icon {
    public static function nf(string $class): string {
        return "<i class='nf $class'></i>";
    }
}
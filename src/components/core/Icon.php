<?php

namespace components\core;

class Icon {
    public static function nf(string $class, ?string $fallback = null): string {
        if (is_null($fallback)) {
            return "<i class='nf $class'></i>";
        }

        return "<i class='nf $class'><span>$fallback</span></i>";
    }

    public static function home(): string {
        return static::nf('nf-fa-home', 'Home');
    }

    public static function edit(): string {
        return static::nf('nf-oct-pencil', 'Edit');
    }

    public static function delete(): string {
        return static::nf('nf-oct-trash', 'Delete');
    }
}
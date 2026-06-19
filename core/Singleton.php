<?php

namespace core;

trait Singleton {
    private static mixed $instance = null;

    public static function getInstance(...$args): self {
        if (is_null(self::$instance)) {
            self::$instance = new self(...$args);
        }

        return self::$instance;
    }
}
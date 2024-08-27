<?php

namespace core;

trait Singleton {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
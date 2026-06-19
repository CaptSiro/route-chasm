<?php

namespace core;

trait MultiSingleton {
    private static array $instances = [];
    private static ?self $instance = null;

    public static function getInstance(...$args): static {
        if (empty($args)) {
            if (is_null(self::$instance)) {
                self::$instance = new self(...$args);
            }

            return self::$instance;
        }

        $key = json_encode($args);
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self(...$args);
        }

        return self::$instances[$key];
    }
}
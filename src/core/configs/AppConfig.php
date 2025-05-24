<?php

namespace core\configs;

use core\Singleton;

class AppConfig {
    use Singleton;

    public static function getConfig(): Config {
        return self::getInstance()->config;
    }

    

    private ?Config $config = null;

    public function set(Config $config): void {
        $this->config = $config;
    }

    public function get(): ?Config {
        return $this->config;
    }
}
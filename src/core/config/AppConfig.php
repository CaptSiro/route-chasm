<?php

namespace core\config;

use core\Singleton;

class AppConfig {
    use Singleton;

    private ?Config $config = null;

    public function set(Config $config): void {
        $this->config = $config;
    }

    public function get(): ?Config {
        return $this->config;
    }
}
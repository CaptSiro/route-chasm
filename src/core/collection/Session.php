<?php

namespace core\collection;

class Session implements StrictDictionary {
    protected bool $isStarted = false;



    public function isStarted(): bool {
        return $this->isStarted;
    }

    protected function start(): void {
        if ($this->isStarted) {
            return;
        }

        session_start();
        $this->isStarted = true;
    }

    public function exists(string $name): bool {
        $this->start();
        return isset($_SESSION[$name]);
    }

    public function set(string $name, mixed $value): void {
        $this->start();
        $_SESSION[$name] = $value;
    }

    public function get(string $name, mixed $or = null): mixed {
        $this->start();
        return $_SESSION[$name] ?? $or;
    }

    public function load(array $array): void {
        $this->start();
        foreach ($array as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function getStrict(string $name): mixed {
        $this->start();
        if (!isset($_SESSION[$name])) {
            throw new NotDefinedException($name);
        }

        return $_SESSION[$name];
    }

    public function toArray(): array {
        return $_SESSION;
    }

    public function remove(string $name): mixed {
        $value = $this->get($name);
        unset($_SESSION[$name]);
        return $value;
    }
}
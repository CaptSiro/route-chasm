<?php

namespace core\collections\dictionary;

use core\collections\StrictDictionary;
use core\utils\Strings;
use models\core\Domain\Domain;

/**
 * @template-implements StrictDictionary<mixed>
 */
class Session implements StrictDictionary {
    protected bool $isStarted = false;



    public function __construct(
        protected Domain $domain
    ) {}



    public function isStarted(): bool {
        return $this->isStarted;
    }

    protected function start(): void {
        if ($this->isStarted) {
            return;
        }

        session_set_cookie_params([
            'path' => Strings::prepend('/', $this->domain->path),
        ]);
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

    public function copy(): static {
        return new static($this->domain);
    }

    public function clear(): void {
        // todo
        //  - Add enum SessionPolicy that is configurable to either call session_unset(), call session_destroy(),
        //    or throw NotAllowedException
        session_unset();
    }
}
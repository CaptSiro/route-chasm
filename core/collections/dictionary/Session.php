<?php

namespace core\collections\dictionary;

use core\collections\StrictDictionary;
use core\utils\Strings;
use models\Domain\Domain;

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

        // [Claude review] Added httponly/samesite/secure. Without HttpOnly any XSS (e.g. via an uploaded HTML file
        // served from /fs) could read the session cookie directly; SameSite=Lax blocks cross-site POSTs carrying the
        // admin session (the app has no CSRF tokens); Secure is set only when the request itself came over HTTPS so
        // local plain-HTTP development keeps working.
        session_set_cookie_params([
            'path' => Strings::prepend('/', $this->domain->path),
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
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
        // [Claude review] start() was missing, so calling toArray() first read an undefined $_SESSION (warning ->
        // 500 via the global error handler, then TypeError on the array return type).
        $this->start();
        return $_SESSION;
    }

    /**
     * Issues a new session id while keeping the data. Must be called when the privilege
     * level changes (login/logout) to prevent session fixation: otherwise an attacker who planted a known session
     * id in the victim's browser is logged in as the victim once they authenticate.
     */
    public function regenerate(): void {
        $this->start();
        session_regenerate_id(true);
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
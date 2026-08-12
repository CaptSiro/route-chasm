<?php

namespace core;

use Closure;

trait Dispatcher {
    protected array $listeners = [];



    public function on(string $event, Closure $function): void {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [$function];
            return;
        }

        $this->listeners[$event][] = $function;
    }

    public function dispatch(string $event, mixed $context): void {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            $listener($context);
        }
    }
}
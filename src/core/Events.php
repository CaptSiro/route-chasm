<?php

namespace core;

use Closure;

trait Events {
    private ?array $handlers = null;

    public function on(string $event, Closure $handler): static {
        if (is_null($this->handlers)) {
            $this->handlers[$event] = $handler;
        }

        return $this;
    }

    public function dispatch(string $event, mixed $data): void {
        if (isset($this->handlers[$event])) {
            ($this->handlers[$event])($data);
        }
    }
}

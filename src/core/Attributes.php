<?php

namespace core;

use core\utils\Arrays;

trait Attributes {
    protected array $attributes = [];

    public function addAttribute(string $name, mixed $value = null): self {
        $this->attributes[$name] = $value ?? $name;
        return $this;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getHtmlAttributes(): string {
        return Arrays::htmlEncode($this->attributes);
    }

    public function getAttribute(string $name): mixed {
        return $this->attributes[$name] ?? null;
    }
}
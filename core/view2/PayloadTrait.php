<?php

namespace core\view2;

trait PayloadTrait {
    protected array $properties = [];



    public function __get(string $name) {
        return $this->properties[$name];
    }

    public function __set(string $name, $value): void {
        $this->properties[$name] = $value;
    }

    public function set(string $property, mixed $value): static {
        $this->properties[$property] = $value;
        return $this;
    }



    // RendererPayload
    public function getViewReference(): View {
        return $this;
    }

    public function get(string $property): mixed {
        return $this->properties[$property] ?? null;
    }

    public function has(string $property): bool {
        return isset($this->properties[$property]);
    }

    public function all(): array {
        return $this->properties;
    }
}
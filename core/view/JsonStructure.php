<?php

namespace core\view;

use JsonSerializable;

class JsonStructure implements JsonSerializable, View {
    /**
     * @param array<string, mixed> $structure
     */
    public function __construct(
        protected array $structure = []
    ) {}



    public function set(string $name, mixed $value): static {
        $this->structure[$name] = $value;
        return $this;
    }

    public function setRef(string $name, mixed &$value): static {
        $this->structure[$name] = &$value;
        return $this;
    }



    // View
    public function render(): string {
        return json_encode($this->jsonSerialize());
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return $this->structure;
    }
}
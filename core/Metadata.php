<?php

namespace core;

trait Metadata {
    protected array $metadata = [];

    public function getMetadata(string $key): mixed {
        return $this->metadata[$key] ?? null;
    }

    public function setMetadata(string $key, mixed $value): void {
        $this->metadata[$key] = $value;
    }
}
<?php

namespace core\cache;

interface Cache {
    public function has(string $variable): bool;

    public function get(string $variable): string;

    public function set(string $variable, string $value): self;

    public function save(): self;
}
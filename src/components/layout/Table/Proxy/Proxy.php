<?php

namespace components\layout\Table\Proxy;

interface Proxy {
    public function setItem(mixed $item): void;

    public function getValue(string $name): string;
}
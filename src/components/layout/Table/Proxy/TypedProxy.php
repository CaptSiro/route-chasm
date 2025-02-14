<?php

namespace components\layout\Table\Proxy;

class TypedProxy implements Proxy {
    protected mixed $item;

    public function setItem(mixed $item): void {
        $this->item = $item;
    }

    public function getValue(string $name): string {
        $value = $this->item->$name ?? null;

        return match (gettype($value)) {
            "string" => $value,
            "boolean" => $value ? 'true' : 'false',
            "integer", "double" => ''. $value,
            "array" => implode(', ', $value),
            "object" => json_encode($value),
            default => '',
        };
    }
}
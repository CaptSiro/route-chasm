<?php

namespace components\layout\Grid\Proxy;

use components\core\Html\Html;

class TypeProxy implements Proxy {
    protected mixed $item;

    public function setItem(mixed $item): void {
        $this->item = $item;
    }

    public function getValue(string $name): string {
        $value = $this->item->$name ?? null;

        // wrap is a safe-function, no need to Html::safe it
        return Html::wrap('span', match (gettype($value)) {
            "string" => $value,
            "boolean" => $value ? 'Yes' : 'No',
            "integer", "double" => ''. $value,
            "array" => implode(', ', $value),
            "object" => json_encode($value),
            default => '',
        });
    }
}
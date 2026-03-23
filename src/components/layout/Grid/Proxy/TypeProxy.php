<?php

namespace components\layout\Grid\Proxy;

use components\core\Html\Html;
use core\utils\Strings;

class TypeProxy implements Proxy {
    protected mixed $item;

    public function setItem(mixed $item): void {
        $this->item = $item;
    }

    public function getItem(): mixed {
        return $this->item;
    }

    public function getValueUnwrapped(string $name): string {
        return Strings::toHumanReadable($this->item->$name ?? null);
    }

    public function getValue(string $name): string {
        return Html::wrap('span', $this->getValueUnwrapped($name));
    }
}
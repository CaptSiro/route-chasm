<?php

namespace core\database\column;

use core\database\Column;
use http\Exception\InvalidArgumentException;

class ForeignKey implements Column {
    public function __construct(
        protected string $class,
        protected string $alias
    ) {
        if (!method_exists($class, "fromId")) {
            throw new InvalidArgumentException("Provided class '$class' must implement 'fromId' method");
        }
    }



    public function transform(mixed $value): mixed {
        return call_user_func("$this->class::fromId", $value);
    }

    public function isVirtual(mixed $value): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getAlias(): string {
        return $this->alias;
    }

    public function isAutoCreated(): bool {
        return false;
    }
}
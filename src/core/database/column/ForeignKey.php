<?php

namespace core\database\column;

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

    public function isVirtual(): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getAlias(): string {
        return $this->alias;
    }
}
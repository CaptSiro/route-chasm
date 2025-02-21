<?php

namespace core\database\sql\parameter;

use core\database\buffer\ParamBuffer;
use core\database\Param;

class SqlPrimitiveParam implements Param {
    use SqlParamType;

    public const IDENT = "?";



    /**
     * Basic implementation for primitive types such as string, number or NULL
     *
     * @param mixed $value
     */
    public function __construct(
        protected mixed $value
    ) {}



    public function stringify(): string {
        return "[?]: '" . $this->value . "' (" . $this->getType() . ")";
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed {
        return $this->value;
    }

    public function getName(): ?string {
        return null;
    }

    public function __toString(): string {
        ParamBuffer::getInstance()
            ->add($this);
        return "?";
    }
}
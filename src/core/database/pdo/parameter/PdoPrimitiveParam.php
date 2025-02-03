<?php

namespace core\database\pdo\parameter;

use core\database\buffer\ParamBuffer;
use core\database\Param;

class PdoPrimitiveParam implements Param {
    use PdoParamType;

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
<?php

namespace core\database\sql\parameter;

use core\database\buffer\ParamBuffer;
use core\database\Param;
use http\Exception\InvalidArgumentException;

class SqlNamedParam implements Param {
    use SqlParamType;

    protected string $name;



    public function __construct(
        string $name,
        protected mixed $value
    ) {
        if (strlen($name) < 1) {
            throw new InvalidArgumentException("Name must be at least one character long");
        }

        $this->name = $name[0] !== ':'
            ? ":$name"
            : $name;
    }



    function __toString(): string {
        ParamBuffer::getInstance()
            ->add($this);
        return $this->getName();
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed {
        return $this->value;
    }

    function stringify(): string {
        return "[" . $this->name . "]: '" . $this->value . "' (" . $this->getType() . ")";
    }
}
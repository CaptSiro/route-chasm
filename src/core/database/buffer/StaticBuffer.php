<?php

namespace core\database\buffer;

use BadFunctionCallException;
use core\database\parameter\Param;
use core\database\parameter\Primitive;

readonly class StaticBuffer implements Buffer {
    public const PARAM_IDENT = Primitive::IDENT;

    public static function from(array $values): self {
        return new self(array_map(fn($x) => new Primitive($x), $values));
    }



    public function __construct(
        protected array $params
    ) {}



    function add(Param $value): Buffer {
        throw new BadFunctionCallException("Cannot modify static buffer");
    }

    function shift(): Param {
        return array_shift($this->params);
    }

    function isEmpty(): bool {
        return empty($this->params);
    }

    function dump(): array {
        return $this->params;
    }

    function load(array $values): Buffer {
        throw new BadFunctionCallException("Cannot modify static buffer");
    }
}